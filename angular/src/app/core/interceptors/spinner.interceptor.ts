import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { finalize } from 'rxjs';

// Import services
import { SpinnerService } from '@shared/services/utils/spinner.service';

export const spinnerInterceptor: HttpInterceptorFn = (req, next) => {
  const spinnerService = inject(SpinnerService);

  spinnerService.show();

  return next(req).pipe(
    finalize(() => spinnerService.hide())
  );
};