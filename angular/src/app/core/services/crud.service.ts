import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { HttpResponseData, OptionalQueryParams } from '../types/api.interface';
import { ApiService } from './api.service';

/**
 * Abstract base service providing RESTful CRUD operations
 * @template T - The entity type this service handles
 */
@Injectable()
export abstract class CrudService<T> {
  /** API endpoint path for this resource */
  protected abstract endpoint: string;

  /** HTTP service for API communication */
  protected apiService: ApiService = inject(ApiService);

  /**
   * Get all records from the API
   * @returns Observable with array of records
   */
  public index(params?: OptionalQueryParams<T>): Observable<HttpResponseData<T[]>> {
    return this.apiService.get<T[], T>(this.endpoint, params);
  }

  /**
   * Get a single record by ID
   * @param id - Record identifier
   * @returns Observable with single record
   */
  public show(id: number, params?: OptionalQueryParams<T>): Observable<HttpResponseData<T>> {
    return this.apiService.get<T, T>(`${this.endpoint}/${id}`, params);
  }

  /**
   * Create a new record
   * @param data - Partial data for the new record
   * @returns Observable with created record
   */
  public create(
    data: Partial<T>,
    params?: OptionalQueryParams<T>,
  ): Observable<HttpResponseData<T>> {
    return this.apiService.post<T>(this.endpoint, data, params);
  }

  /**
   * Update an existing record
   * @param id - Record identifier
   * @param data - Partial data to update
   * @returns Observable with updated record
   */
  public update(
    id: number,
    data: Partial<T>,
    params?: OptionalQueryParams<T>,
  ): Observable<HttpResponseData<T>> {
    return this.apiService.put<T>(`${this.endpoint}/${id}`, data, params);
  }

  /**
   * Delete a record by ID
   * @param id - Record identifier
   * @returns Observable with deletion response
   */
  public delete(id: number): Observable<HttpResponseData<object>> {
    return this.apiService.delete<HttpResponseData<object>>(`${this.endpoint}/${id}`);
  }

  /**
   * Getter to expose the endpoint property for testing or external configuration purposes.
   * @returns The configured endpoint string.
   */
  public getEndpoint(): string {
    return this.endpoint;
  }
}
