import { TestBed } from '@angular/core/testing';
import { firstValueFrom, Observable, of } from 'rxjs';
import { ApiService } from '@core/services/api.service';
import { CrudService } from '@core/services/crud.service';
import { HttpResponseData } from '@core/types/api.interface';
import { Entity, Identifiable } from '@shared/entities/base/entity.interface';
import { EntityFactory } from '../factories/base/entity.factory';

/**
 * Abstract class defining the contract for CRUD service testing.
 * This class should be extended by concrete service spec files (e.g., AddressServiceSpec).
 * @template T - The entity type (e.g., Address, User, Role).
 */
export abstract class BaseCrudServiceSpec<T extends Identifiable & Entity> {
  /** Intance of the service being tested */
  protected service!: CrudService<T>;

  /** Spy object for the dependency ApiService */
  protected mockApiService!: jasmine.SpyObj<ApiService>;

  /** Instance of the factory for creating mock data */
  protected abstract factory: EntityFactory<T>;

  /** Endpoint for the service being tested */
  protected abstract endpoint: string;

  /**
   * Public setter to allow the test suite to inject the concrete service instance.
   * @param serviceInstance - The instance of the service under test.
   */
  public setService(serviceInstance: CrudService<T>): void {
    this.service = serviceInstance;
  }

  /**
   * Public getter to retrieve the concrete service instance for custom tests.
   */
  public getService(): CrudService<T> {
    return this.service;
  }

  /**
   * Public setter to allow the test suite to inject the mock ApiService spy.
   */
  public setApiService(apiServiceSpy: jasmine.SpyObj<ApiService>): void {
    this.mockApiService = apiServiceSpy;
  }

  /**
   * Defines and runs the core CRUD test suite for the concrete service.
   */
  public runTests() {
    beforeEach(() => {
      this.mockApiService = TestBed.inject(ApiService) as jasmine.SpyObj<ApiService>;
    });

    // --- INDEX() ---
    it('should call GET method on the correct endpoint and return an array', async () => {
      const mockRecords = this.factory.createArray(3);
      const mockResponse: HttpResponseData<T[]> = { data: mockRecords, message: 'List retrieved' };
      this.mockApiService.get.and.returnValue(
        of(mockResponse) as Observable<HttpResponseData<T[]>>,
      );

      const response = await firstValueFrom(this.service.index());

      // Verification: Action
      expect(this.mockApiService.get).toHaveBeenCalledTimes(1);
      expect(this.mockApiService.get).toHaveBeenCalledWith(this.endpoint, undefined);

      // Verification: Data Integrity
      expect(response.data.length).toBe(mockRecords.length);
      mockRecords.forEach((expectedRecord, index) => {
        expect(response.data[index].id).toEqual(expectedRecord.id);
      });
    });

    // --- SHOW(id) ---
    it('should call GET method with ID on the correct endpoint and return a single record', async () => {
      const testId = 42;
      const mockRecord = this.factory.create({ id: testId } as Partial<T>);
      const mockResponse: HttpResponseData<T> = { data: mockRecord, message: 'Record retrieved' };
      this.mockApiService.get.and.returnValue(of(mockResponse) as Observable<HttpResponseData<T>>);

      const response = await firstValueFrom(this.service.show(testId));

      expect(this.mockApiService.get).toHaveBeenCalledTimes(1);
      expect(this.mockApiService.get).toHaveBeenCalledWith(`${this.endpoint}/${testId}`, undefined);

      expect(response.data.id).toEqual(testId);
    });

    // --- CREATE(data) ---
    it('should call POST method with data and return the created record with ID', async () => {
      const newId = 99;
      const mockFullRecord = this.factory.create({ id: newId } as Partial<T>);
      const { id, ...newDataSent } = mockFullRecord;
      const mockResponse: HttpResponseData<T> = { data: mockFullRecord, message: 'Created' };

      this.mockApiService.post.and.returnValue(of(mockResponse) as Observable<HttpResponseData<T>>);

      const response = await firstValueFrom(this.service.create(newDataSent as Partial<T>));

      expect(this.mockApiService.post).toHaveBeenCalledTimes(1);
      expect(this.mockApiService.post).toHaveBeenCalledWith(this.endpoint, newDataSent, undefined);
      expect(response.data.id).toEqual(newId);
    });

    // --- UPDATE(id, data) ---
    it('should call PUT method with ID and data and return the updated record', async () => {
      const testId = 101;
      const updateData = { city: 'Updated City' } as unknown as Partial<T>;
      const initialRecord = this.factory.create({ id: testId } as Partial<T>);
      const mockUpdatedRecord = { ...initialRecord, ...updateData };
      const mockResponse: HttpResponseData<T> = {
        data: mockUpdatedRecord as T,
        message: 'Updated',
      };

      this.mockApiService.put.and.returnValue(of(mockResponse) as Observable<HttpResponseData<T>>);

      const response = await firstValueFrom(this.service.update(testId, updateData));

      expect(this.mockApiService.put).toHaveBeenCalledTimes(1);
      expect(this.mockApiService.put).toHaveBeenCalledWith(
        `${this.endpoint}/${testId}`,
        updateData,
        undefined,
      );
      expect(response.data.id).toEqual(testId);
      expect((response.data as any).city).toEqual('Updated City');
    });

    // --- DELETE(id) ---
    it('should call DELETE method with ID on the correct endpoint', async () => {
      const testId = 200;
      const mockResponse: HttpResponseData<any> = { data: null, message: 'Deleted' };

      this.mockApiService.delete.and.returnValue(
        of(mockResponse) as Observable<HttpResponseData<any>>,
      );

      const response = await firstValueFrom(this.service.delete(testId));

      expect(this.mockApiService.delete).toHaveBeenCalledTimes(1);
      expect(this.mockApiService.delete).toHaveBeenCalledWith(`${this.endpoint}/${testId}`);
      expect(response.data).toBeNull();
    });
  }
}
