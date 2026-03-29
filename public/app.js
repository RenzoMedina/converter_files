const fileInput = document.getElementById("document-file");
const btnConvert = document.getElementById("btn-convert");
const btnReset = document.getElementById("btn-reset");
const btnChange = document.getElementById("btn-change");
const dropZone = document.getElementById("drop-zone");
const uploadContent = document.getElementById("upload-content");
const fileSelectedContent = document.getElementById("file-selected-content");
const fileName = document.getElementById("file-name");
const fileSize = document.getElementById("file-size");
const selectFormat = document.getElementById('output-format');
const lineformat = document.getElementById("line-format");

function formatFileSize(bytes) {
  if (bytes === 0) return "0 Bytes";
  const k = 1024;
  const sizes = ["Bytes", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + " " + sizes[i];
}

function handleFileSelect() {
  if (fileInput.files && fileInput.files[0]) {
    const file = fileInput.files[0];

    /* || file.type === "application/msword" || file.type === "application/vnd.openxmlformats-officedocument.wordprocessingml.document" */
    if (file.type === "application/pdf") {
      fileName.textContent = file.name;
      fileSize.textContent = formatFileSize(file.size);

      uploadContent.classList.add("hidden");
      fileSelectedContent.classList.remove("hidden");
      fileSelectedContent.classList.add("flex");

      dropZone.classList.remove(
        "border-gray-300",
        "hover:border-primary",
        "hover:bg-blue-50",
      );
      dropZone.classList.add("border-green-500", "bg-green-50");

      btnConvert.classList.remove("hidden");
      btnReset.classList.remove("hidden");
/*       selectFormat.classList.remove("hidden");
      lineformat.classList.remove("hidden"); */
    } else {
      showAlert("Por favor selecciona un archivo compatible", 7000, "error");
      fileInput.value = "";
      resetView();
    }
  }
}

function resetView() {
  uploadContent.classList.remove("hidden");
  fileSelectedContent.classList.add("hidden");
  fileSelectedContent.classList.remove("flex");
  dropZone.classList.remove("border-green-500", "bg-green-50");
  dropZone.classList.add(
    "border-gray-300",
    "hover:border-primary",
    "hover:bg-blue-50",
  );
  btnConvert.classList.add("hidden");
  btnReset.classList.add("hidden");
/*   selectFormat.classList.add("hidden");
  lineformat.classList.add("hidden"); */
  fileInput.value = "";
}

fileInput.addEventListener("change", handleFileSelect);

btnChange.addEventListener("click", (e) => {
  e.stopPropagation();
  resetView();
  fileInput.click();
});

btnReset.addEventListener("click", (e) => {
  e.stopPropagation();
  resetView();
});

let dragCounter = 0;

dropZone.addEventListener("dragenter", (e) => {
  e.preventDefault();
  e.stopPropagation();
  dragCounter++;
  dropZone.classList.add("border-primary", "bg-blue-100", "scale-105");
  dropZone.classList.remove("border-gray-300", "border-green-500");
});

dropZone.addEventListener("dragleave", (e) => {
  e.preventDefault();
  e.stopPropagation();
  dragCounter--;
  if (dragCounter === 0) {
    dropZone.classList.remove("border-primary", "bg-blue-100", "scale-105");
    if (fileInput.files && fileInput.files[0]) {
      dropZone.classList.add("border-green-500", "bg-green-50");
    } else {
      dropZone.classList.add("border-gray-300");
    }
  }
});

dropZone.addEventListener("dragover", (e) => {
  e.preventDefault();
  e.stopPropagation();
});

dropZone.addEventListener("drop", (e) => {
  e.preventDefault();
  e.stopPropagation();
  dragCounter = 0;

  dropZone.classList.remove("border-primary", "bg-blue-100", "scale-105");

  if (e.dataTransfer.files.length) {
    fileInput.files = e.dataTransfer.files;
    handleFileSelect();
  }
});

document
  .getElementById("document-file")
  .addEventListener("change", function () {
    const fileName = this.files[0]
      ? this.files[0].name
      : "Ningún archivo seleccionado";
    document.querySelector(".file-button").textContent = fileName;
  });

setTimeout(() => {
  window.history.replaceState({}, document.title, window.location.pathname);
}, 7000);

const url = new URLSearchParams(window.location.search);
const alertBox = document.getElementById("alert");
const alertMessage = document.getElementById("alert-message");
const alertIcon = document.getElementById("alert-icon");

const showAlert = (message, duration, type) => {
  alertBox.className = `fixed top-4 right-4 max-w-md p-4 rounded-lg shadow-lg transition-all duration-500 ease-in-out z-50 border-2 alert-${type}`;

  const icons = {
    error: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>`,
    success: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>`,
    info: `<svg class="w-6 h-6 icon-info" fill="currentColor" viewBox="0 0 20 20">
                 <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
               </svg>`,
    warning: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
              </svg>`,
  };

  alertIcon.innerHTML = icons[type];
  alertMessage.innerHTML = message;

  setTimeout(() => alertBox.classList.add("translate-x-0"), 100);
  setTimeout(() => closeAlert(), duration);
};

function closeAlert() {
  alertBox.classList.remove("translate-x-0");
  alertBox.classList.add("translate-x-[120%]");
}

if (url.has("success")) {
  const total = url.get("total");
  showAlert(
    `¡Archivo horneado y servido! Descárgalo cuando quieras <br> Total de ${total} preguntas creadas`,
    7000,
    "success",
  );
}

const errorType = url.get("error");
const errorMsg = url.get("msg");

if (errorType) {
  const mensajes = {
    "sin-preguntas": "No se encontraron preguntas válidas en el documento, ya que todas presentan problemas de formato o falta de información.",
    "formato-invalido": errorMsg || "El formato del documento no es reconocido",
    "no-preguntas": "No se encontraron preguntas en el documento, ya que todas presentan problemas de formato o falta de información.",
    "no-preguntas-indicadores":
      "No se encontraron preguntas en los indicadores, ya que todas presentan problemas de formato o falta de información.",
    procesamiento: errorMsg || "Error al procesar el archivo",
    "not-file":
      "El archivo no existe o no se pudo encontrar, vuelve a intentarlo",
    "error-zip": "Error al crear el archivo ZIP, por favor intenta de nuevo",
    "error-download":
      "Error al descargar el archivo, por favor intenta de nuevo",
    fatal: errorMsg || "Ocurrió un error inesperado al procesar el documento",
    default: errorMsg || "Ha ocurrido un error",
  };

  const mensaje = mensajes[errorType] || mensajes["default"];
  showAlert(mensaje, 7000, "error");
}

const loader = document.getElementById("loader");
document.getElementById("formulario").addEventListener("submit", function (e) {
  loader.classList.remove("hidden");
  loader.classList.add("flex");
});

window.addEventListener("load", function () {
  if (!this.sessionStorage.getItem("alertShown")) {
    showAlert(
      "! Importante ! Por motivos de seguridad, tu archivo solo podrá descargarse una vez. Si aparece algún error durante la descarga, simplemente vuelve a convertir tu documento. No almacenamos tus archivos para proteger tu información.",
      10000,
      "info",
    );
    this.sessionStorage.setItem("alertShown", "true");
  }
});
