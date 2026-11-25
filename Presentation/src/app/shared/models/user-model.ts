export interface UserModel {
  id?: number;
  username: string;
  password?: string;
  rol_id: number;
  rol?: string;
  activo?: boolean | number;
}