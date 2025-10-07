// codes.ts
export class Code {
  idcodes!: number;
  idBrand!: number;
  brandName!: string;           // si ya lo agregaste por el JOIN
  code!: string;
  description!: string;
  status!: boolean | number | string;  // acepta "1"/"0" también
  createdAt!: string;
  validUntil!: string;

  /** Normaliza a booleano real */
  get isActive(): boolean {
    return Number(this.status) === 1;
  }

  /** Texto del estado */
  get statusLabel(): string {
    return this.isActive ? 'Active' : 'Inactive';
  }

  /** Clase correcta para badges en BS5 */
  get statusClass(): string {
    return this.isActive ? 'text-bg-success' : 'text-bg-secondary';
  }
}
