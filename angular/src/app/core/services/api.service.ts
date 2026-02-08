import { HttpClient, HttpErrorResponse, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { MatSnackBar } from '@angular/material/snack-bar';
import { catchError, Observable, tap, throwError } from 'rxjs';
import { HttpResponseData, OptionalQueryParams } from '../types/api.interface';

/**
 * Generic HTTP service for API communication
 */
@Injectable({
  providedIn: 'root',
})
export class ApiService {
  /** Base URL for all API requests */
  private baseUrl = '/api';

  /** The service used to make HTTP requests */
  private http: HttpClient = inject(HttpClient);

  /** The service used to display notifications */
  private snackBar: MatSnackBar = inject(MatSnackBar);

  /**
   * Perform HTTP GET request
   * @param endpoint - API endpoint path
   * @param params - Query parameters
   * @returns Observable with response data
   */
  public get<T, TParams>(
    endpoint: string,
    params: OptionalQueryParams<TParams> = null,
  ): Observable<HttpResponseData<T>> {
    const httpParams = this.buildParams(params);

    return this.http
      .get<HttpResponseData<T>>(`${this.baseUrl}/${endpoint}`, {
        params: httpParams,
      })
      .pipe(
        catchError((error: HttpErrorResponse) => {
          this.showError(error);
          return throwError(() => error);
        }),
      );
  }

  /**
   * Perform HTTP POST request
   * @param endpoint - API endpoint path
   * @param data - Request body data
   * @param params - Query parameters
   * @returns Observable with response data
   */
  public post<T>(
    endpoint: string,
    data: Partial<T>,
    params: OptionalQueryParams<T> = null,
  ): Observable<HttpResponseData<T>> {
    const httpParams = this.buildParams(params);

    return this.http
      .post<HttpResponseData<T>>(`${this.baseUrl}/${endpoint}`, data, {
        params: httpParams,
      })
      .pipe(
        tap(() => this.showSuccess('Record created successfully')),
        catchError((error: HttpErrorResponse) => {
          this.showError(error);
          return throwError(() => error);
        }),
      );
  }

  /**
   * Perform HTTP PUT request
   * @param endpoint - API endpoint path
   * @param data - Request body data
   * @param params - Query parameters
   * @returns Observable with response data
   */
  public put<T>(
    endpoint: string,
    data: Partial<T>,
    params: OptionalQueryParams<T> = null,
  ): Observable<HttpResponseData<T>> {
    const httpParams = this.buildParams(params);

    return this.http
      .put<HttpResponseData<T>>(`${this.baseUrl}/${endpoint}`, data, {
        params: httpParams,
      })
      .pipe(
        tap(() => this.showSuccess('Record updated successfully')),
        catchError((error: HttpErrorResponse) => {
          this.showError(error);
          return throwError(() => error);
        }),
      );
  }

  /**
   * Perform HTTP DELETE request
   * @param endpoint - API endpoint path
   * @returns Observable with response data
   */
  public delete<T>(endpoint: string): Observable<HttpResponseData<T>> {
    return this.http.delete<HttpResponseData<T>>(`${this.baseUrl}/${endpoint}`).pipe(
      tap(() => this.showSuccess('Record deleted successfully')),
      catchError((error: HttpErrorResponse) => {
        this.showError(error);
        return throwError(() => error);
      }),
    );
  }

  /**
   * Build the parameters send with the request
   * @param params The parameters to send
   * @returns HttpParams
   */
  private buildParams<T>(params: OptionalQueryParams<T>): HttpParams {
    let httpParams = new HttpParams();

    if (params) {
      for (const key in params) {
        const value = params[key as keyof OptionalQueryParams<T>];

        if (value !== null && typeof value !== 'undefined') {
          // If the value is an object like 'where'
          if (typeof value === 'object' && !Array.isArray(value)) {
            Object.keys(value).forEach((nestedKey) => {
              const nestedValue = value[nestedKey as keyof T];

              if (nestedValue !== null && typeof nestedValue !== 'undefined') {
                // Build the key with the syntax 'where[user_id]'
                const fullKey = `${key}[${nestedKey}]`;

                // Convert the value to string safely
                httpParams = httpParams.set(fullKey, String(nestedValue));
              }
            });
          } else {
            // If the value is a primitive (with, withCount, sort)
            httpParams = httpParams.set(key, String(value));
          }
        }
      }
    }

    return httpParams;
  }

  /**
   * Show error snackbar notification
   * @param error - HTTP error response
   * @returns void
   */
  private showError(error: HttpErrorResponse): void {
    let errorMessage = 'An error occurred';

    if (error.error?.message) {
      errorMessage = error.error.message;
    } else if (error.error?.errors) {
      const errors = Object.values(error.error.errors).flat();
      errorMessage = errors.join(', ');
    } else if (error.message) {
      errorMessage = error.message;
    }

    this.snackBar.open(errorMessage, 'Close', {
      duration: 5000,
      horizontalPosition: 'center',
      verticalPosition: 'bottom',
      panelClass: 'error-snackbar',
    });
  }

  /**
   * Show success snackbar notification
   * @param message - Success message to display
   * @returns void
   */
  private showSuccess(message: string): void {
    this.snackBar.open(message, 'Close', {
      duration: 3000,
      horizontalPosition: 'center',
      verticalPosition: 'bottom',
      panelClass: 'success-snackbar',
    });
  }
}
