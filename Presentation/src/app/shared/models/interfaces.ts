// Lo que Negocio realmente devuelve en /auth/login y /auth/refresh-session/{id}.
export interface LoginResponse {
  token: string;
  tkRef: string;
}

export interface DialogData {
  titulo: string;
  mensaje: string;
  textoConfirmar?: string;
  textoCancelar?: string;
}