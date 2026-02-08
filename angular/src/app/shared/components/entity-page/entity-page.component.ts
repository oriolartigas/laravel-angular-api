// Import third-party
import {
  ChangeDetectorRef,
  Directive,
  EventEmitter,
  inject,
  OnDestroy,
  OnInit,
  Type,
  ViewChild,
} from '@angular/core';
import { MatDialog, MatDialogRef } from '@angular/material/dialog';
import { catchError, EMPTY, Observable, Subject, Subscription, switchMap, take, tap } from 'rxjs';

// Import types
import { HttpResponseData, OptionalQueryParams } from '../../../core/types/api.interface';
import { Entity, Identifiable } from '../../entities/base/entity.interface';
import { ColumnType, EntityTableConfig, TableColumn } from '../../types/entity-table.interface';
import {
  PicklistConfig,
  PicklistDialogData,
  PickListDialogReturnData,
  PicklistItem,
} from '../../types/picklist-dialog.interface';
import { FormAction, HighlightRowId } from '../../types/types';

// Import services
import { CrudService } from '../../../core/services/crud.service';
import { PicklistDialogComponent } from '../dialogs/picklist-dialog/picklist-dialog.component';
import { EntityTableComponent } from '../entity-table/entity-table.component';

// Import utils
import { FormDialogData } from '../../types/form-dialog.interface';
import { sortByProperty } from '../../utils/sort.utils';

@Directive()
export abstract class EntityPageComponent<
  T extends Identifiable & Entity,
  FormDialog extends {
    saveForm: EventEmitter<T>;
  },
  ServiceType extends CrudService<T>,
>
  implements OnInit, OnDestroy
{
  /** Inject EditableTableComponent to access its methods */
  @ViewChild(EntityTableComponent) editableTableComponent!: EntityTableComponent<T>;

  /** The label for the create button */
  protected abstract createButtonLabel: string;

  /** The dialog used to create new records */
  protected abstract formDialogComponent: Type<FormDialog>;

  /** The service for CRUD operations */
  protected abstract getService(): ServiceType;

  /** The columns used in the table */
  protected abstract getColumns(): TableColumn<T>[];

  /** Get the data used to create a new record */
  protected abstract getCreateDialogData(): Partial<T>;

  /** The payload used to update */
  protected abstract getUpdatePayload(item: T): Partial<T>;

  /** Default sort key used in the table */
  protected abstract getDefaultSortKey(): string;

  /** The action of the dialog (Create or Update) */
  private action: FormAction = FormAction.Create;

  /** Subscription to the create dialog */
  private createSubscriptions = new Subscription();

  /** Inject ChangeDetectorRef to force to detect changes in the view */
  protected cdr: ChangeDetectorRef = inject(ChangeDetectorRef);

  /** Inject MatDialog to create dialogs */
  protected dialog = inject(MatDialog);

  /** The table configuration */
  protected tableConfig: EntityTableConfig = {
    can_edit: true,
    can_delete: true,
    can_select: false,
  };

  /** Column configuration for the table */
  protected columns: TableColumn<T>[] = [];

  /** The list of items to display in the table */
  protected items: T[] = [];

  /** The id of the item to highlight in the table */
  protected highlightItemId!: HighlightRowId;

  /**
   * Initialize component and load records from API
   */
  ngOnInit(): void {
    this.columns = this.getColumns();
    this.getData();
  }

  /**
   * Load records from API
   */
  private getData(): void {
    const params = this.getIndexParams();

    this.getService()
      .index(params)
      .pipe(take(1))
      .subscribe({
        next: (response: HttpResponseData<T[]>) => {
          this.items = response.data.sort(sortByProperty(this.getDefaultSortKey()));
        },
      });
  }

  /**
   * Open the create dialog and subscribe
   * to the events of the dialog.
   */
  openCreateDialog(): void {
    this.action = FormAction.Create;

    const dialogRef = this.dialog.open<FormDialog, FormDialogData<T>, boolean>(
      this.formDialogComponent,
      {
        panelClass: 'create-dialog-panel',
        disableClose: true,
        data: {
          action: this.action,
          item: this.getCreateDialogData(),
        },
      },
    );

    const instance = dialogRef.componentInstance as FormDialog;

    this.subscribeToCreateComponent(instance, dialogRef);
  }

  openEditDialog(item: T): void {
    this.action = FormAction.Update;

    const dialogRef = this.dialog.open<FormDialog, FormDialogData<T>, boolean>(
      this.formDialogComponent,
      {
        panelClass: 'create-dialog-panel',
        disableClose: true,
        data: {
          action: this.action,
          item: item,
        },
      },
    );

    const instance = dialogRef.componentInstance as FormDialog;

    this.subscribeToCreateComponent(instance, dialogRef);
  }

  /**
   * Get the available and assigned relations,
   * then open the picklist dialog, update the entity,
   * and if the update was successful, close the dialog
   * and refresh the view.
   *
   * @param entityToUpdate The entity being updated
   * @param fetchAvailableService The service to get available items used to assign relations
   * @param updateService The service used to update the entity
   * @param config The configuration for the picklist dialog
   * @returns An observable with the updated entity
   */
  protected openPicklistDialog<T extends Identifiable, U extends Identifiable>(
    entityToUpdate: T,
    getAvailableItemsService: { index(): Observable<HttpResponseData<U[]>> },
    updateService: {
      update(
        id: number,
        payload: Partial<T>,
        options: OptionalQueryParams<T>,
      ): Observable<HttpResponseData<T>>;
    },
    config: PicklistConfig<T, U>,
  ): Observable<HttpResponseData<T>> {
    let dialogRef: MatDialogRef<PicklistDialogComponent, PickListDialogReturnData>;

    return getAvailableItemsService.index().pipe(
      switchMap((response: HttpResponseData<U[]>) => {
        const idsSubject = new Subject<number[]>();

        const availableItems: PicklistItem[] = response.data.map(config.mapToPicklistItem);
        const selectedIds: number[] = config.getCurrentAssignedIds(entityToUpdate);

        const columns: TableColumn<T>[] = [
          { key: 'id', label: 'ID', type: ColumnType.Text },
          { key: 'name', label: 'Name', type: ColumnType.Text },
        ];

        dialogRef = this.dialog.open<
          PicklistDialogComponent,
          PicklistDialogData<T>,
          PickListDialogReturnData
        >(PicklistDialogComponent, {
          disableClose: true,
          autoFocus: false,
          height: config.height ?? '475px',
          width: config.width ?? '650px',
          data: {
            entity: config.dialogEntityName,
            columns: columns,
            availableItems: availableItems,
            assignedIds: selectedIds,
            returnSubject: idsSubject,
          },
        });

        return idsSubject.pipe(
          switchMap((ids: number[] | undefined) => {
            /** Get the response of the picklist dialog and update the entity */

            if (!ids) {
              return EMPTY;
            }

            const payload = { [config.assignedIdKey]: ids } as Partial<T>;
            const withCount = { withCount: config.countRelation };

            return updateService.update(entityToUpdate.id!, payload, withCount).pipe(
              tap(() => dialogRef.close()),
              catchError((err) => {
                console.log(err);
                return EMPTY;
              }),
            );
          }),
        );
      }),
    );
  }

  /**
   * Subscribe to the events of the create dialog
   * to be able to control the creation of new records
   * and decide when to close the dialog.
   *
   * @param instance The instance of the dialog
   * @param dialogRef The reference of the dialog
   */
  private subscribeToCreateComponent(
    instance: FormDialog,
    dialogRef: MatDialogRef<FormDialog, boolean>,
  ): void {
    this.createSubscriptions.unsubscribe();
    this.createSubscriptions = new Subscription();
    this.createSubscriptions.add(
      instance.saveForm.subscribe((data: T) => {
        if (this.action === FormAction.Create) {
          this.createItem(data, dialogRef);
        } else {
          this.updateItem(data, dialogRef);
        }
      }),
    );

    dialogRef
      .afterClosed()
      .pipe(take(1))
      .subscribe(() => {
        this.createSubscriptions.unsubscribe();
      });
  }

  /**
   * Create new record.
   *
   * @param itemData The data to create
   * @param dialogRef The dialog reference
   * @returns void
   */
  createItem(itemData: T, dialogRef: MatDialogRef<FormDialog, boolean>): void {
    const params = this.getCreateParams();

    this.getService()
      .create(itemData, params)
      .pipe(take(1))
      .subscribe({
        next: (response: HttpResponseData<T>) => {
          this.items.push(response.data);
          this.items.sort(sortByProperty(this.getDefaultSortKey()));
          this.highlightRow(response.data.id!);

          dialogRef.close(true);
        },
      });
  }

  /**
   * Update record.
   *
   * Event executed when the user click the save button of a row.
   * @param item The item to update
   * @returns void
   */
  updateItem(item: T, dialogRef: MatDialogRef<FormDialog, boolean>): void {
    this.getService()
      .update(item.id!, this.getUpdatePayload(item), this.getIndexParams())
      .pipe(take(1))
      .subscribe({
        next: (response: HttpResponseData<T>) => {
          // Update the item to refresh the view
          this.items = this.items.map((item: T) => {
            if (item.id === response.data.id) {
              return response.data;
            }
            return item;
          });

          this.highlightRow(response.data.id!);

          dialogRef.close(true);
        },
      });
  }

  /**
   * Delete record.
   *
   * Event executed when the user click the delete button of a row.
   * @param item The item to delete
   * @returns void
   */
  deleteItem(item: T): void {
    this.getService()
      .delete(item.id)
      .pipe(take(1))
      .subscribe({
        next: (_response: HttpResponseData<object>) => {
          this.items = this.items.filter((i: T) => i.id !== item.id);
        },
      });
  }

  /**
   * Default parameters used in the index method of the service.
   * @returns OptionalQueryParams
   */
  protected getIndexParams(): OptionalQueryParams<T> {
    return null;
  }

  /**
   * Default parameters used in the create method of the service.
   * @returns OptionalQueryParams
   */
  protected getCreateParams(): OptionalQueryParams<T> {
    return null;
  }

  /**
   * Highlight a row forcing the refresh
   * and using a timestamp to force the change
   * detection even when the ID is the same
   * @param id The ID of the item
   */
  private highlightRow(id: number): void {
    this.cdr.detectChanges();

    // Delay the highlight until the DOM exists
    setTimeout(() => {
      this.highlightItemId = {
        id: id,
        timestamp: Date.now(),
      };
    });
  }

  /**
   * Unsubcribe from all subscriptions
   */
  ngOnDestroy(): void {
    this.createSubscriptions.unsubscribe();
  }
}
