# 🗂️ Convertidor PDF a XML para Moodle

Proyecto modular en PHP para convertir archivos PDF a XML compatibles con Moodle. Diseñado para integrarse fácilmente en flujos CI/CD, con despliegue reproducible en Docker, monitoreo con supervisord 



## 🚀 Características

- Conversión automática de archivos PDF a XML estructurado
- Arquitectura modular con rutas definidas en PHP nativo
- Contenedor Docker optimizado para entornos reproducibles
- Supervisión de procesos con `supervisord`
- Listo para integración con pipelines CI/CD y monitoreo
- Estilos personalizados con CSS y TailwindCSS

## 📁 Estructura del proyecto

``` plaintext
├── app/        
├── public/ 
├── routes/  
├── index.php  
├── nginx.conf  
├── supervisord.conf  
└── Dockerfile

```


## ⚙️ Requisitos

- Docker & Docker Compose  
- PHP >= 8.0  
- Composer
- NodeJS
- TailwindCSS


## 🐳 Despliegue con Docker

``` bash
docker build -t nombre .
docker-compose up -d

```
    
## 🌐 Escalabilidad futura

- Integración con Moodle vía API REST o WebService

- Conversión en lote de múltiples PDFs

- Interfaz web para subir archivos y visualizar resultados

- ChatOps para notificar conversiones exitosas o fallidas

## 🖼️ Demo
![Imagen del proyecto](/public/assets/img/demo_project.png)

## Authors
- Backend Developer & DevOps Jr
- [@renzomedina](https://github.com/RenzoMedina)

