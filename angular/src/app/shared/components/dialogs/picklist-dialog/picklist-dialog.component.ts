// Import third-party
import { Component, inject, OnInit } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import {
  MAT_DIALOG_DATA,
  MatDialogActions,
  MatDialogClose,
  MatDialogContent,
  MatDialogTitle,
} from '@angular/material/dialog';
import { MatIconModule } from '@angular/material/icon';

//  Import types
import { EntityTableConfig, TableColumn } from '../../../types/entity-table.interface';
import { PicklistDialogData, PicklistItem } from '../../../types/picklist-dialog.interface';

// Import components
import { sortByProperty } from '../../../utils/sort.utils';
import { EntityTableComponent } from '../../entity-table/entity-table.component';

@Component({
  selector: 'app-picklist-dialog',
  standalone: true,
  imports: [
    MatButtonModule,
    MatDialogActions,
    MatDialogClose,
    MatDialogContent,
    MatDialogTitle,
    MatIconModule,
    EntityTableComponent,
  ],
  templateUrl: './picklist-dialog.component.html',
  styleUrls: ['./picklist-dialog.component.css'],
})
export class PicklistDialogComponent implements OnInit {
  public title: string = 'Assign relations';

  /** Column configuration for the table */
  public columns: TableColumn<PicklistItem>[] = [];

  /** Column configuration for the table */
  protected availableTableConfig: EntityTableConfig = {
    can_edit: false,
    can_delete: false,
    can_select: true,
    caption: 'Available items',
  };

  /** Column configuration for the table */
  protected assignedTableConfig: EntityTableConfig = {
    can_edit: false,
    can_delete: false,
    can_select: true,
    caption: 'Assigned items',
  };

  /** The data passed to the dialog */
  private data = inject<PicklistDialogData<PicklistItem>>(MAT_DIALOG_DATA);

  /** The list of items to display in the available table */
  protected availableItems: PicklistItem[] = [];

  /** The list of items to display in the assigned table */
  protected assignedItems: PicklistItem[] = [];

  /** The original list of items to display in the table */
  protected originalAssignedItems: PicklistItem[] = [];

  /** Whether there are any items selected in the available table */
  public hasItemsSelectedInAvailableTable: boolean = false;

  /** Whether there are any items selected in the assigned table */
  public hasItemsSelectedInAssignedTable: boolean = false;

  /** The selected items in the available table */
  public selectedAvailableItems: PicklistItem[] = [];

  /** The selected items in the assigned table */
  public selectedAssignedItems: PicklistItem[] = [];

  /**
   * Prepare the data and the autocomplete
   */
  ngOnInit() {
    this.columns = this.data.columns;
    this.availableTableConfig.caption = 'Available ' + this.data.entity;
    this.assignedTableConfig.caption = 'Assigned ' + this.data.entity;
    this.title = 'Assign ' + this.data.entity;

    this.getData();
  }

  /**
   * Get the available and assigned items used
   * to fill the tables and save the initial
   * assigned items to compare them and determine
   * if the save button should be disabled.
   */
  private getData(): void {
    this.availableItems = this.data.availableItems.filter(
      (item: PicklistItem) => !this.data.assignedIds.includes(item.id),
    );

    this.assignedItems = this.data.availableItems.filter((item: PicklistItem) =>
      this.data.assignedIds.includes(item.id),
    );
    this.originalAssignedItems = [...this.assignedItems];
  }

  /**
   * When the selected items changes in the available table
   * @param selection
   */
  onAvailableSelectionChange(selection: PicklistItem[]): void {
    this.selectedAvailableItems = selection;
    this.hasItemsSelectedInAvailableTable = selection.length > 0;
  }

  /**
   * When the selected items changes in the assigned table
   * @param selection
   */
  onAssignedSelectionChange(selection: PicklistItem[]): void {
    this.selectedAssignedItems = selection;
    this.hasItemsSelectedInAssignedTable = selection.length > 0;
  }

  /**
   * Move the selected items to the assigned table
   */
  moveItemsToAssignedTable() {
    this.transfer(this.availableItems, this.assignedItems, this.selectedAvailableItems, true);
  }

  /**
   * Move the selected items to the available table
   */
  moveItemsToAvailableTable() {
    this.transfer(this.assignedItems, this.availableItems, this.selectedAssignedItems, false);
  }

  /**
   * Move all available items to the assigned table
   */
  moveAllItemsToAssignedTable() {
    this.transfer(this.availableItems, this.assignedItems, this.availableItems, true);
  }

  /**
   * Move all assigned items to the available table
   */
  moveAllItemsToAvailableTable() {
    this.transfer(this.assignedItems, this.availableItems, this.assignedItems, false);
  }

  /**
   * Manage the transfer of items between tables.
   *
   * @param sourceItems The source array of items
   * @param destinationItems The destination array of items
   * @param itemsToMove The items to move
   * @param isForward Whether the movement is forward (to the assigned table) or backwards (to the available table)
   */
  private transfer(
    sourceItems: PicklistItem[],
    destinationItems: PicklistItem[],
    itemsToMove: PicklistItem[],
    isForward: boolean,
  ): void {
    if (itemsToMove.length === 0) {
      return;
    }

    const selectedIds = itemsToMove.map((item) => item.id);

    const newDestination = [...destinationItems, ...itemsToMove];
    const newSource = sourceItems.filter((item) => !selectedIds.includes(item.id));

    if (isForward) {
      this.assignedItems = newDestination.sort(sortByProperty('name'));
      this.availableItems = newSource;

      // Clean the selection only if we didn't use the moveAll
      if (itemsToMove !== this.availableItems) {
        this.selectedAvailableItems = [];
        this.hasItemsSelectedInAvailableTable = false;
      }
    } else {
      this.availableItems = newDestination.sort(sortByProperty('name'));
      this.assignedItems = newSource;

      // Clean the selection only if we didn't use the moveAll
      if (itemsToMove !== this.assignedItems) {
        this.selectedAssignedItems = [];
        this.hasItemsSelectedInAssignedTable = false;
      }
    }
  }

  /**
   * Check if the save button should be disabled or not,
   * if the original items are the same as assigned items.
   * @returns bool
   */
  saveIsDisabled(): boolean {
    const originalIds = this.originalAssignedItems.map((item: PicklistItem) => item.id).sort();
    const selectedIds = this.assignedItems.map((item: PicklistItem) => item.id).sort();

    if (originalIds.length !== selectedIds.length) {
      return false;
    }

    for (let i = 0; i < originalIds.length; i++) {
      if (originalIds[i] !== selectedIds[i]) {
        return false;
      }
    }

    return true;
  }

  /**
   * Save the selected items into the returnSubject
   * and don't close the dialog to allow the parent
   * component to handle the close event.
   */
  save(): void {
    const selectedIds = this.assignedItems.map((item: PicklistItem) => item.id);

    this.data.returnSubject.next(selectedIds);
  }
}
