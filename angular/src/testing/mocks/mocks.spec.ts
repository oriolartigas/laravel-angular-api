import { MatDialogRef } from '@angular/material/dialog';
import { of } from 'rxjs';

/**
 * Helper to test opening of create or edit dialogs in a component.
 * Subscribes to the dialog's save observable and asserts the saved item.
 *
 * @template T - The type of the item handled by the dialog.
 * @param componentFn - Function returning the component instance.
 * @param dialogSpyFn - Function returning the `MatDialog` spy.
 * @param itemOrFactory - Either an instance of T or a factory with `create()` method.
 * @param isCreate - True if testing a create dialog, false for edit dialog.
 * @param done - Jasmine `DoneFn` callback for async completion.
 */
export const mockFormDialog = <T>(
  componentFn: () => any,
  dialogSpyFn: () => jasmine.SpyObj<any>,
  itemOrFactory: T | { create: () => T },
  isCreate: boolean,
  done: DoneFn,
) => {
  const component = componentFn();
  const dialogSpy = dialogSpyFn();

  const item: T = isCreate ? (itemOrFactory as { create: () => T }).create() : (itemOrFactory as T);

  const dialogRef: Partial<MatDialogRef<any>> = {
    afterClosed: () => of(item),
    close: jasmine.createSpy('close'),
    componentInstance: { saveForm: of(item) },
  };

  dialogSpy.open.and.returnValue(dialogRef as MatDialogRef<any>);

  if (isCreate) {
    component['openCreateDialog']();
  } else {
    component['openEditDialog'](item);
  }

  dialogRef.componentInstance.saveForm.subscribe((savedItem: T) => {
    expect(savedItem).toEqual(item);

    const expectedData = jasmine.objectContaining({
      data: jasmine.objectContaining({
        item: isCreate ? jasmine.any(Object) : item,
        action: isCreate ? 'create' : 'update',
      }),
    });

    expect(dialogSpy.open).toHaveBeenCalledWith(component.formDialogComponent, expectedData);

    done();
  });
};
