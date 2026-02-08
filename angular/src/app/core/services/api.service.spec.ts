import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { MatSnackBar } from '@angular/material/snack-bar';
import { HttpResponseData } from '../types/api.interface';
import { ApiService } from './api.service';

describe('ApiService', () => {
  let service: ApiService;
  let httpMock: HttpTestingController;
  let snackBarSpy: jasmine.SpyObj<MatSnackBar>;

  beforeEach(() => {
    const spy = jasmine.createSpyObj('MatSnackBar', ['open']);

    TestBed.configureTestingModule({
      providers: [
        provideHttpClient(),
        provideHttpClientTesting(),
        { provide: MatSnackBar, useValue: spy },
      ],
    });

    service = TestBed.inject(ApiService);
    httpMock = TestBed.inject(HttpTestingController);
    snackBarSpy = TestBed.inject(MatSnackBar) as jasmine.SpyObj<MatSnackBar>;
  });

  afterEach(() => {
    httpMock.verify();
  });

  describe('GET requests', () => {
    it('should perform GET request', () => {
      const mockData = { data: [{ id: 1, name: 'Test' }] };

      service.get('users').subscribe((data) => {
        expect(data).toEqual(mockData);
      });

      const req = httpMock.expectOne('/api/users');
      expect(req.request.method).toBe('GET');
      req.flush(mockData);
    });

    it('should handle query parameters', () => {
      service.get('users', { with: 'roles', withCount: 'roles' }).subscribe();

      const req = httpMock.expectOne('/api/users?with=roles&withCount=roles');
      expect(req.request.method).toBe('GET');
      req.flush({});
    });

    it('should handle nested parameters', () => {
      service.get('users', { where: { status: 'active' } }).subscribe();

      const req = httpMock.expectOne('/api/users?where%5Bstatus%5D=active');
      expect(req.request.method).toBe('GET');
      req.flush({});
    });
  });

  describe('POST requests', () => {
    it('should perform POST request and show success message', () => {
      const postData = { name: 'New User' };
      const mockResponse: HttpResponseData<{ id: number; name: string }> = {
        data: { id: 1, name: 'New User' },
      };

      service.post<{ id: number; name: string }>('users', postData).subscribe((data) => {
        expect(data).toEqual(mockResponse);
      });

      const req = httpMock.expectOne('/api/users');
      expect(req.request.method).toBe('POST');
      expect(req.request.body).toEqual(postData);
      req.flush(mockResponse);

      expect(snackBarSpy.open).toHaveBeenCalledWith(
        'Record created successfully',
        'Close',
        jasmine.objectContaining({ panelClass: 'success-snackbar' }),
      );
    });
  });

  describe('PUT requests', () => {
    it('should perform PUT request and show success message', () => {
      const putData = { name: 'Updated User' };
      const mockResponse: HttpResponseData<{ id: number; name: string }> = {
        data: { id: 1, name: 'Updated User' },
      };

      service.put('users/1', putData).subscribe((data) => {
        expect(data).toEqual(mockResponse);
      });

      const req = httpMock.expectOne('/api/users/1');
      expect(req.request.method).toBe('PUT');
      expect(req.request.body).toEqual(putData);
      req.flush(mockResponse);

      expect(snackBarSpy.open).toHaveBeenCalledWith(
        'Record updated successfully',
        'Close',
        jasmine.objectContaining({ panelClass: 'success-snackbar' }),
      );
    });
  });

  describe('DELETE requests', () => {
    it('should perform DELETE request and show success message', () => {
      service.delete('users/1').subscribe();

      const req = httpMock.expectOne('/api/users/1');
      expect(req.request.method).toBe('DELETE');
      req.flush({});

      expect(snackBarSpy.open).toHaveBeenCalledWith(
        'Record deleted successfully',
        'Close',
        jasmine.objectContaining({ panelClass: 'success-snackbar' }),
      );
    });
  });

  describe('Error handling', () => {
    it('should handle HTTP errors and show error message', () => {
      const errorResponse = { message: 'User not found' };

      service.get('users/999').subscribe({
        next: () => fail('Should have failed'),
        error: (error) => {
          expect(error.error).toEqual(errorResponse);
        },
      });

      const req = httpMock.expectOne('/api/users/999');
      req.flush(errorResponse, { status: 404, statusText: 'Not Found' });

      expect(snackBarSpy.open).toHaveBeenCalledWith(
        'User not found',
        'Close',
        jasmine.objectContaining({ panelClass: 'error-snackbar' }),
      );
    });

    it('should handle validation errors', () => {
      const errorResponse = {
        errors: {
          name: ['Name is required'],
          email: ['Email is invalid'],
        },
      };

      service.post('users', {}).subscribe({
        next: () => fail('Should have failed'),
        error: () => {},
      });

      const req = httpMock.expectOne('/api/users');
      req.flush(errorResponse, { status: 422, statusText: 'Unprocessable Entity' });

      expect(snackBarSpy.open).toHaveBeenCalledWith(
        'Name is required, Email is invalid',
        'Close',
        jasmine.objectContaining({ panelClass: 'error-snackbar' }),
      );
    });
  });
});
