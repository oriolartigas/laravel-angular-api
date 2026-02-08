import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting } from '@angular/common/http/testing';
import { EnvironmentProviders, Provider, Type } from '@angular/core';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { MatDialogModule } from '@angular/material/dialog';
import { MatSnackBar } from '@angular/material/snack-bar';
import { of } from 'rxjs';
import { CrudService } from '../../app/core/services/crud.service';
import { OptionalQueryParams } from '../../app/core/types/api.interface';
import { Entity, Identifiable } from '../../app/shared/entities/base/entity.interface';
import { ColumnType, TableColumn } from '../../app/shared/types/entity-table.interface';
import { EntityFactory } from '../factories/base/entity.factory';
import { EntityPageTestHarness } from '../mixins/expose-entity-page.mixin';
import { mockServiceClass } from '../mocks/mock-crud-service.spec';

/**
 * Configuration object for EntityPageTestSuite.
 * @template T - The entity type being tested, must extend Identifiable & Entity.
 */
export interface EntityPageTestConfig<T extends Identifiable & Entity> {
  // Name of the test
  describeName: string;
  // The class of the real component to test
  ComponentType: Type<any>;
  // The class with the exposed harness
  HarnessType: new () => EntityPageTestHarness<T>;
  // The main entity service
  EntityService: Type<CrudService<T>>;
  // The secondary service
  EntitySecondaryService?: Type<any>;
  // The mock secondary service
  MockSecondaryService?: Type<any>;
  // The factory to create mock data
  Factory: EntityFactory<T>;
  // The secondary factory
  secondaryFactory?: EntityFactory<T>;
  // The expected index params
  expectedIndexParams: OptionalQueryParams<T> | null;
  // Function to generate payload
  getUpdatePayload: (item: T) => Partial<T>;
  // Function to check columns
  columnCheck?: (columns: TableColumn<T>[], ComponentType: Type<any>) => void;
  // Optional overrides
  indexOverrides?: Partial<T>;
  showOverrides?: Partial<T>;
  updateOverrides?: Partial<T>;
  secondaryIndexOverrides?: Partial<any>;
  secondaryShowOverrides?: Partial<any>;
  secondaryUpdateOverrides?: Partial<any>;
}

/**
 * Generic test suite for entity pages.
 * Sets up the component with mock services, initializes the component,
 * and runs common tests for CRUD functionality.
 *
 * @template T - The entity type being tested.
 * @param config - Configuration object for the test suite.
 */
export function EntityPageTestSuite<T extends Identifiable & Entity>(
  config: EntityPageTestConfig<T>,
) {
  describe(config.describeName, () => {
    let fixture: ComponentFixture<InstanceType<typeof config.HarnessType>>;
    let component: EntityPageTestHarness<T>;

    let service: CrudService<T>;
    let secondaryService: any;

    beforeEach(async () => {
      const providers: (Provider | EnvironmentProviders)[] = [
        {
          provide: config.EntityService,
          useClass: mockServiceClass(config.EntityService, config.Factory, {
            index: config.indexOverrides,
            show: config.showOverrides,
            update: config.updateOverrides,
          }),
        },
        provideHttpClient(),
        provideHttpClientTesting(),
        { provide: MatSnackBar, useValue: { open: () => {} } },
      ];

      if (config.EntitySecondaryService) {
        providers.push({
          provide: config.EntitySecondaryService,
          useClass: mockServiceClass(config.EntitySecondaryService, config.secondaryFactory!, {
            index: config.secondaryIndexOverrides,
            show: config.secondaryShowOverrides,
          }),
        });
      }

      await TestBed.configureTestingModule({
        imports: [config.HarnessType, MatDialogModule],
        providers: providers,
      }).compileComponents();

      fixture = TestBed.createComponent(config.HarnessType);
      component = fixture.componentInstance as EntityPageTestHarness<T>;
      service = TestBed.inject(config.EntityService);

      if (config.EntitySecondaryService) {
        secondaryService = TestBed.inject(config.EntitySecondaryService);
      }

      (service.index as jasmine.Spy).and.returnValue(of({ data: [config.Factory.create()] }));

      fixture.detectChanges();
    });

    it('should create the component', () => expect(component).toBeTruthy());

    it('should call getColumns and load data on init', () => {
      expect(service.index).toHaveBeenCalledWith(component.expose_getIndexParams());
      expect(component.expose_columns().length).toBeGreaterThan(0);
      expect(component.expose_items().length).toBe(1);
    });

    it('should return correct table column definitions (general check)', () => {
      const columns = component.expose_columns();

      if (config.columnCheck) {
        config.columnCheck(columns, config.ComponentType);
      } else {
        expect(columns.length).toBeGreaterThan(0);
      }
    });

    it('should return correct index parameters', () => {
      const expected = config.expectedIndexParams ?? component.expose_getIndexParams();
      expect(component.expose_getIndexParams()).toEqual(expected);
    });

    it('should generate correct update payload', () => {
      const mockItem = config.Factory.create();
      const payload = component.expose_getUpdatePayload(mockItem);

      expect(payload).toEqual(config.getUpdatePayload(mockItem));
    });
  });
}

/**
 * Generic helper to check table columns.
 *
 * @template T - Entity type
 * @param columns - Array of TableColumn<T> to check
 * @param expectedColumns - Array of expected columns with key and type
 */
export function checkColumns<T extends Identifiable>(
  columns: TableColumn<T>[],
  expectedColumns: { key: string; type: ColumnType }[],
) {
  expectedColumns.forEach((expected) => {
    const col = columns.find((c) => c.key === expected.key);
    expect(col).toBeTruthy();
    expect(col!.type).toBe(expected.type);

    if (expected.type === 'button') {
      expect(col!.onClick).toBeInstanceOf(Function);
    }
  });
}
