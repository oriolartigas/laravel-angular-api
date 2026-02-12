// Import third-party
import { CommonModule } from '@angular/common';
import {
  Component,
  ElementRef,
  EventEmitter,
  Input,
  OnInit,
  Output,
  ViewChild,
  inject,
} from '@angular/core';
import { FormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { MatIconModule } from '@angular/material/icon';

// Import types
import { Identifiable } from '../../entities/base/entity.interface';
import { EntityTableConfig, TableColumn } from '../../types/entity-table.interface';

// Import services
import { ScreenSizeService } from '@core/services/screen-size.service';

// Import components
import { ScreenSize } from '@core/types/types';
import { HighlightRowId } from '../../types/types';
import { ConfirmDialogComponent } from '../dialogs/confirm-dialog/confirm-dialog.component';
import { SpinnerService } from '@shared/services/utils/spinner.service';

@Component({
  selector: 'app-entity-table',
  standalone: true,
  imports: [CommonModule, FormsModule, MatButtonModule, MatIconModule, MatDialogModule],
  templateUrl: './entity-table.component.html',
  styleUrls: ['./entity-table.component.css'],
})
export class EntityTableComponent<T extends Identifiable> implements OnInit {
  @ViewChild('scrollContainer') scrollContainer!: ElementRef<HTMLElement>;

  /** Array of entity objects to display in the table */
  @Input() data: T[] = [];

  /** Table configuration */
  @Input() config: EntityTableConfig = {
    can_edit: false,
    can_delete: false,
    can_select: false,
    caption: '',
  };

  /** Column configuration */
  @Input() columns: TableColumn<T>[] = [];

  /** Array of selected external items */
  @Input() selectedExternalItems: T[] = [];

  /** Highlight the item with the specified ID */
  @Input()
  set highlightItemId(value: HighlightRowId | undefined) {
    if (value && typeof value.id === 'number') {
      this._highlightItemId = value.id;
      this.highlightRow(value.id);
    }
  }

  @Output() rowEdited = new EventEmitter<T>();

  /** Event emitted when an item is deleted */
  @Output() rowDeleted = new EventEmitter<T>();

  /** Event emitted when the selection changes */
  @Output() rowSelected = new EventEmitter<T[]>();

  /** Services */
  private dialog = inject(MatDialog);
  private screenSizeService: ScreenSizeService = inject(ScreenSizeService);
  private readonly spinnerService = inject(SpinnerService);

  /** ID of the item to highlight */
  private _highlightItemId: number | undefined;

  /** Expose the observable to the HTML */
  protected isLoading$ = this.spinnerService.visibility$;

  /** Array of selected items */
  public selectedItems: T[] = [];

  /** The device of the user */
  public ScreenSize: ScreenSize = 'desktop';

  ngOnInit(): void {
    this.screenSizeService.deviceType$.subscribe((type) => {
      this.ScreenSize = type;
    });
  }

  get highlightItemId(): number | undefined {
    return this._highlightItemId;
  }

  /**
   * Columns to render based on device size.
   *
   * If the device is a desktop or tablet, show all columns.
   * If the device is a mobile, show only the first two columns.
   * @returns TableColumn[]
   */
  get visibleColumns(): TableColumn<T>[] {
    if (this.ScreenSize === 'desktop' || this.ScreenSize === 'tablet') {
      return this.columns;
    }

    const visible = this.columns.slice(0, 2);

    return visible;
  }

  /**
   * When a row is added, highlight the item
   * with the specified ID and scroll to it
   */
  private highlightRow(id: number): void {
    const element = document.getElementById(`row-${id}`);

    if (element) {
      element.classList.add('highlight');

      const container = this.scrollContainer?.nativeElement;

      if (container) {
        const elementOffsetTop = element.offsetTop;
        const containerHeight = container.clientHeight;
        const targetScrollTop = elementOffsetTop - containerHeight / 2 + element.clientHeight / 2;

        container.scrollTo({
          top: targetScrollTop,
          behavior: 'smooth',
        });
      }

      setTimeout(() => {
        element.classList.remove('highlight');
      }, 2500);
    }
  }

  /**
   * Emit an event to open the edit dialog
   * @param id - ID of the item to edit
   */
  edit(item: T): void {
    this.rowEdited.emit(item);
  }

  /**
   * Delete an item with confirmation dialog
   * @param item - The item to delete
   */
  delete(item: T): void {
    const dialogRef = this.dialog.open(ConfirmDialogComponent, {
      data: {
        title: 'Confirm Delete',
        message: 'Are you sure you want to delete this item?',
      },
    });

    dialogRef.afterClosed().subscribe((result) => {
      if (result) {
        this.rowDeleted.emit(item);
      }
    });
  }

  /**
   * Toggle selection for an item when the row is clicked
   * @param event The event emitted by the click
   * @param item The item to toggle selection for
   * @returns void
   */
  toggleRowSelection(event: MouseEvent, item: T): void {
    if (!this.config.can_select) {
      return;
    }

    const target = event.target as HTMLElement;

    // Avoid selection if the click is on an interactive element
    const isInteractive = target.closest('button, input:not([type="checkbox"]), a, mat-icon');

    // Avoid selection if the click is on the checkbox
    const isCheckbox = target instanceof HTMLInputElement && target.type === 'checkbox';

    if (isInteractive || isCheckbox) {
      return;
    }

    this.toggleSelection(item);
  }

  /**
   * Event emitted when the selection changes
   * and the array of selected items is updated
   * and emitted
   * @param item - The item to toggle selection for
   */
  toggleSelection(item: T): void {
    const index = this.selectedExternalItems.findIndex((i) => i.id === item.id);

    if (index > -1) {
      this.selectedExternalItems.splice(index, 1);
    } else {
      this.selectedExternalItems.push(item);
    }

    this.rowSelected.emit(this.selectedExternalItems);
  }

  /**
   * Track function for ngFor to improve performance
   * @param index - Array index
   * @param item - Data item
   * @returns Unique identifier for the item
   */
  trackById(item: T): number {
    return item.id;
  }

  /**
   * Get formatted display value for a table cell.
   * If the column key includes '_at', it formats the value as a date
   * @param item - Data item
   * @param column - Column configuration
   * @returns Formatted string value
   */
  getDisplayValue(item: T, column: TableColumn<T>): string | unknown {
    if (!item || !column.key) {
      return '';
    }

    if (column.key.includes('.')) {
      const keys = column.key.split('.');

      let currentValue: unknown = item;

      for (const key of keys) {
        if (currentValue && currentValue.hasOwnProperty(key)) {
          if (typeof currentValue === 'object' && currentValue !== null && key in currentValue) {
            currentValue = (currentValue as Record<string, unknown>)[key];
          }
        } else {
          return '';
        }
      }

      return currentValue;
    } else {
      const value = item[column.key];

      if (
        column.key.includes('_at') &&
        (typeof value === 'string' || typeof value === 'number' || value instanceof Date)
      ) {
        return new Date(value).toLocaleString();
      }

      return value;
    }
  }
}
