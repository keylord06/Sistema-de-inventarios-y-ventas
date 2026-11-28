// Business devuelve { error: "mensaje" } o { errors: { campo: "mensaje" } }.
// Toma el primer mensaje legible para mostrarlo en el formulario.
export function mensajeDeError(err: any): string {
  const cuerpo = err?.error;
  if (cuerpo?.error) {
    return cuerpo.error;
  }
  if (cuerpo?.errors) {
    const primerMensaje = Object.values(cuerpo.errors)[0];
    if (typeof primerMensaje === 'string') {
      return primerMensaje;
    }
  }
  return 'Ocurrió un error. Intenta de nuevo.';
}
