// Import third-party
import { AfterViewInit, Component, inject, OnInit, signal, ViewChild } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatSidenav, MatSidenavModule } from '@angular/material/sidenav';
import { MatSnackBarModule } from '@angular/material/snack-bar';
import { MatToolbarModule } from '@angular/material/toolbar';
import { RouterModule, RouterOutlet } from '@angular/router';
import { take } from 'rxjs';

// Import services
import { ApiService } from '@core/services/api.service';
import { ScreenSizeService } from '@core/services/screen-size.service';
import { SpinnerComponent } from '@shared/components/spinner/spinner.component';

// Import types
import { HttpResponseData } from '@core/types/api.interface';
import { ScreenSize } from '@core/types/types';

export interface VersionResponse {
  version: string;
}

@Component({
  selector: 'app-root',
  imports: [
    RouterOutlet,
    RouterModule,
    MatToolbarModule,
    MatButtonModule,
    MatIconModule,
    MatSidenavModule,
    MatSnackBarModule,
    SpinnerComponent,
  ],
  templateUrl: './app.html',
  styleUrl: './app.css',
})
export class App implements OnInit, AfterViewInit {
  @ViewChild('sidenav') sidenav!: MatSidenav;

  /** Screen size service */
  private screenSizeService: ScreenSizeService = inject(ScreenSizeService);

  /** HTTP client */
  private apiService: ApiService = inject(ApiService);

  /** Title of the application */
  protected readonly title = signal('Laravel Angular API');

  /** Array of menu items */
  public readonly menuItems = [
    { title: 'Users', link: '/users' },
    { title: 'Roles', link: '/roles' },
    { title: 'Addresses', link: '/addresses' },
  ];

  /** Device type (desktop, tablet, mobile) */
  public deviceType: ScreenSize = 'desktop';

  /** Version of the application */
  public version!: string;

  ngOnInit(): void {
    this.getAppVersion();
  }

  /** Close the sidenav when the screen size changes */
  ngAfterViewInit(): void {
    this.screenSizeService.deviceType$.subscribe((dt) => {
      this.deviceType = dt;

      if (this.sidenav) {
        this.sidenav.close();
      }
    });
  }

  /**
   * Get the version of the application
   * @returns void
   */
  private getAppVersion(): void {
    this.apiService
      .get<VersionResponse, void>('version')
      .pipe(take(1))
      .subscribe({
        next: (response: HttpResponseData<VersionResponse>) => {
          this.version = response.data.version;
        },
        error: (err) => {
          console.error(err);
        },
      });
  }
}
