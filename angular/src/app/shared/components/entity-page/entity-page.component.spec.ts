import { Component, EventEmitter, Injectable, Type } from '@angular/core';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { MatDialogModule } from '@angular/material/dialog';
import { of } from 'rxjs';
import { ApiService } from '../../../core/services/api.service';
import { CrudService } from '../../../core/services/crud.service';
import { Entity, Identifiable } from '../../entities/base/entity.interface';
import { ColumnType, TableColumn } from '../../types/entity-table.interface';
import { EntityPageComponent } from './entity-page.component';

interface MockEntity extends Identifiable, Entity {
  id: number;
  name: string;
}

@Component({ template: '' })
class MockDialogComponent {
  saveForm = new EventEmitter<MockEntity>();
}

class MockApiService {
  get = jasmine.createSpy('get').and.returnValue(of({ data: [] }));
  post = jasmine.createSpy('post').and.returnValue(of({ data: { id: 1, name: 'New Item' } }));
  put = jasmine.createSpy('put').and.returnValue(of({ data: { id: 1, name: 'Updated Item' } }));
  delete = jasmine.createSpy('delete').and.returnValue(of({}));
}

@Injectable()
class MockService extends CrudService<MockEntity> {
  protected endpoint = 'mock-endpoint';

  override index = jasmine.createSpy('index').and.returnValue(of({ data: [] }));
  override show = jasmine.createSpy('show').and.returnValue(of({ data: { id: 1, name: 'Item' } }));
  override create = jasmine
    .createSpy('create')
    .and.returnValue(of({ data: { id: 1, name: 'New Item' } }));
  override update = jasmine
    .createSpy('update')
    .and.returnValue(of({ data: { id: 1, name: 'Updated Item' } }));
  override delete = jasmine.createSpy('delete').and.returnValue(of({}));
}

@Component({ selector: 'app-entity-table', template: '' })
class MockEditableTableComponent {}

@Component({
  selector: 'app-test-crud',
  template: '<app-entity-table></app-entity-table>',
  imports: [MockEditableTableComponent],
})
class TestCrudComponent extends EntityPageComponent<MockEntity, MockDialogComponent, MockService> {
  protected createButtonLabel = 'Add Test Item';
  protected formDialogComponent: Type<any> = MockDialogComponent;

  constructor(private mockService: MockService) {
    super();
  }

  protected getService(): MockService {
    return this.mockService;
  }

  protected getColumns(): TableColumn[] {
    return [{ key: 'name', label: 'Name', type: ColumnType.Text }];
  }

  protected getCreateDialogData(): Partial<MockEntity> {
    return { name: 'New Default Name' };
  }

  protected getUpdatePayload(item: MockEntity): Partial<MockEntity> {
    return { name: item.name };
  }

  protected getDefaultSortKey(): string {
    return 'name';
  }
}

describe('BaseCrudComponent', () => {
  let component: TestCrudComponent;
  let fixture: ComponentFixture<TestCrudComponent>;
  let mockService: MockService;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [MatDialogModule, TestCrudComponent, MockEditableTableComponent],
      providers: [{ provide: ApiService, useClass: MockApiService }, MockService],
    }).compileComponents();

    mockService = TestBed.inject(MockService);
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(TestCrudComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create the component and load data on initialization', () => {
    expect(component).toBeTruthy();
    expect((component as any).columns.length).toBeGreaterThan(0);
    expect(mockService.index).toHaveBeenCalled();
  });
});
