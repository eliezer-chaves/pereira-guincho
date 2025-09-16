import { Injectable } from '@angular/core';

@Injectable({ providedIn: 'root' })
export class ObrigadoService {
  private _type: string | null = null;

  setType(type: string) {
    this._type = type;
  }

  getType(): string | null {
    return this._type;
  }

  clear() {
    this._type = null;
  }
}
