export interface LoginResponse {
  token: string;
  refreshToken?: string;
  usuario?: {
    id: number;
    username: string;
    rol_id: number;
    rol?: string;
  };
}

export interface DialogData {
  titulo: string;
  mensaje: string;
  textoConfirmar?: string;
  textoCancelar?: string;
}