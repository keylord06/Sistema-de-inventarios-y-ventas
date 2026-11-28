// El emulador de Android no conoce "localhost" como la PC: 10.0.2.2 es el
// alias especial que usa para llegar a la propia máquina donde corre Docker.
export const environment = {
  production: true,
  apiUrl: 'http://10.0.2.2:9090/api'
};
