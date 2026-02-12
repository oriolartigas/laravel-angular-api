import { CommonModule } from '@angular/common';
import { Component, inject, Input } from '@angular/core';
import { SpinnerService } from '@shared/services/utils/spinner.service';

@Component({
  selector: 'spinner',
  standalone: true,
  imports: [ CommonModule ],
  templateUrl: './spinner.component.html',
  styleUrl: './spinner.component.css',
})
export class SpinnerComponent {
  @Input() text: string = 'Loading...';

  private readonly spinnerService = inject(SpinnerService);

  isLoading$ = this.spinnerService.visibility$;

  constructor() {}
}
