import { ComponentFixture, TestBed } from '@angular/core/testing';
import { FormsModule } from '@angular/forms';

import { Identifiable } from '../../entities/base/entity.interface';
import { ColumnType, TableColumn } from '../../types/entity-table.interface';
import { EntityTableComponent } from './entity-table.component';

interface MockEntity extends Identifiable {
  id: number;
  name: string;
  email?: string;
}

describe('EditableTableComponent', () => {
  let component: EntityTableComponent<MockEntity>;
  let fixture: ComponentFixture<EntityTableComponent<MockEntity>>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [EntityTableComponent, FormsModule],
    }).compileComponents();

    fixture = TestBed.createComponent(EntityTableComponent<MockEntity>);
    component = fixture.componentInstance;
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should display data in table', () => {
    const testData = [{ id: 1, name: 'Test', email: 'test@test.com' }];
    const testColumns: TableColumn[] = [
      { key: 'id', label: 'ID', type: ColumnType.Text },
      { key: 'name', label: 'Name', type: ColumnType.Text },
    ];

    component.data = testData;
    component.columns = testColumns;
    fixture.detectChanges();

    const compiled = fixture.nativeElement;
    expect(compiled.querySelector('td').textContent).toContain('1');
  });

  it('should emit edit event', () => {
    spyOn(component.rowEdited, 'emit');
    const testItem = { id: 1, name: 'Test' };

    component.edit(testItem);

    expect(component.rowEdited.emit).toHaveBeenCalledWith(testItem);
  });
});
