// Import third-party
import { BreakpointObserver } from '@angular/cdk/layout';
import { inject, Injectable } from '@angular/core';
import { map, Observable, shareReplay } from 'rxjs';

// Import types
import { ScreenSize } from '../types/types';

@Injectable({
  providedIn: 'root',
})
export class ScreenSizeService {
  /** Observable que emet el tipus de dispositiu segons la mida de pantalla */
  deviceType$: Observable<ScreenSize>;

  /** Instance of BreakpointObserver */
  private breakpointObserver: BreakpointObserver = inject(BreakpointObserver);

  constructor() {
    this.deviceType$ = this.breakpointObserver
      .observe([
        '(max-width: 767px)',
        '(min-width: 768px) and (max-width: 1023px)',
        '(min-width: 1024px)',
      ])
      .pipe(
        map((result) => {
          if (result.breakpoints['(max-width: 767px)']) {
            return 'mobile';
          }

          if (result.breakpoints['(min-width: 768px) and (max-width: 1023px)']) {
            return 'tablet';
          }

          return 'desktop';
        }),
        shareReplay(1),
      );
  }
}
