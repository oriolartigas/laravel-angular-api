import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class SpinnerService {
  private readonly isLoading$ = new BehaviorSubject<boolean>(false);

  get visibility$(): Observable<boolean> {
    return this.isLoading$.asObservable();
  }

  show(): void {
    this.isLoading$.next(true);
  }

  hide(): void {
    this.isLoading$.next(false);
  }
}
