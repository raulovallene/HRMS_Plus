export interface Brand {
  id: number;
  name: string;
}

export class User {
  idusers!: number;
  idRole!: number;
  username!: string;
  sso!: number;
  password?: string;

  /** A user can belong to multiple brands */
  brands: Brand[] = [];

  constructor(init?: Partial<User>) {
    Object.assign(this, init);
  }

  /** Convenience getter: list brand names as comma-separated text */
  get brandNames(): string {
    return this.brands.map((b) => b.name).join(', ');
  }

  /** Simple role helper (can expand later with enums) */
  get isAdmin(): boolean {
    return this.idRole === 1;
  }
}
