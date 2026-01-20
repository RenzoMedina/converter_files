 const fileInput = document.getElementById('document-file')
    const btnConvert = document.getElementById('btn-convert')
    const btnReset = document.getElementById('btn-reset')
    const btnChange = document.getElementById('btn-change')
    const dropZone = document.getElementById('drop-zone')
    const uploadContent = document.getElementById('upload-content')
    const fileSelectedContent = document.getElementById('file-selected-content')
    const fileName = document.getElementById('file-name')
    const fileSize = document.getElementById('file-size')

    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes'
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB']
        const i = Math.floor(Math.log(bytes) / Math.log(k))
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
    }

    function handleFileSelect() {
        if (fileInput.files && fileInput.files[0]) {
            const file = fileInput.files[0]
            
            if (file.type === 'application/pdf') {
                
                fileName.textContent = file.name
                fileSize.textContent = formatFileSize(file.size)
            
                
                uploadContent.classList.add('hidden')
                fileSelectedContent.classList.remove('hidden')
                fileSelectedContent.classList.add('flex')
                
                
                dropZone.classList.remove('border-gray-300', 'hover:border-primary', 'hover:bg-blue-50')
                dropZone.classList.add('border-green-500', 'bg-green-50')
                
                
                btnConvert.classList.remove('hidden')
                btnReset.classList.remove('hidden')
            } else {
                alert('Por favor selecciona un archivo PDF')
                fileInput.value = ''
                resetView()
            }
        }
    }

    
    function resetView() {
        uploadContent.classList.remove('hidden')
        fileSelectedContent.classList.add('hidden')
        fileSelectedContent.classList.remove('flex')
        dropZone.classList.remove('border-green-500', 'bg-green-50')
        dropZone.classList.add('border-gray-300', 'hover:border-primary', 'hover:bg-blue-50')
        btnConvert.classList.add('hidden')
        btnReset.classList.add('hidden')
        fileInput.value = ''
    }

    
    fileInput.addEventListener('change', handleFileSelect)

    
    btnChange.addEventListener('click', (e) => {
        e.stopPropagation()
        resetView()
        fileInput.click()
    })

    btnReset.addEventListener('click', (e) => {
        e.stopPropagation()
        resetView()
    })
    
    let dragCounter = 0

    dropZone.addEventListener('dragenter', (e) => {
        e.preventDefault()
        e.stopPropagation()
        dragCounter++
        dropZone.classList.add('border-primary', 'bg-blue-100', 'scale-105')
        dropZone.classList.remove('border-gray-300', 'border-green-500')
    });

    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault()
        e.stopPropagation()
        dragCounter--
        if (dragCounter === 0) {
            dropZone.classList.remove('border-primary', 'bg-blue-100', 'scale-105')
            if (fileInput.files && fileInput.files[0]) {
                dropZone.classList.add('border-green-500', 'bg-green-50')
            } else {
                dropZone.classList.add('border-gray-300')
            }
        }
    });

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault()
        e.stopPropagation()
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault()
        e.stopPropagation()
        dragCounter = 0
    
        dropZone.classList.remove('border-primary', 'bg-blue-100', 'scale-105')
        
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files
            handleFileSelect()
        }
    });

document.getElementById('document-file').addEventListener('change', function() {
    const fileName = this.files[0] ? this.files[0].name : 'Ningún archivo seleccionado'
    document.querySelector('.file-button').textContent = fileName
})

setTimeout(() => {
    window.history.replaceState({}, document.title, window.location.pathname)
}, 7000);

const url = new URLSearchParams(window.location.search)
const alertBox = document.getElementById('alert')
const alertMessage = document.getElementById('alert-message')
const alertIcon = document.getElementById('alert-icon')


const showAlert = (message, duration, type) => {
    alertBox.className = `fixed top-4 right-4 max-w-md p-4 rounded-lg shadow-lg transition-all duration-500 ease-in-out z-50 border-2 alert-${type}`
    
     const icons = {
        error: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>`,
        success: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>`,
    };
    
    alertIcon.innerHTML = icons[type] 
    alertMessage.innerHTML = message
    
    setTimeout(() => alertBox.classList.add('translate-x-0'), 100)
    setTimeout(() => closeAlert(), duration)
};

function closeAlert() {
    alertBox.classList.remove('translate-x-0')
    alertBox.classList.add('translate-x-[120%]')
}

if (url.has('success')) {
    const total = url.get('total')
    showAlert(`¡Archivo horneado y servido! Descárgalo cuando quieras <br> Total de ${total} preguntas creadas`, 7000, 'success')
}

if (url.has('error-file')) {
    showAlert("Solo aceptamos archivos PDF por ahora. ¡Gracias!", 7000, 'error')
}

if (url.has('not-file')) {
    showAlert("No existe el archivo", 7000, 'error')
}

const errorType = url.get('error')
const errorMsg = url.get('msg')

if (errorType) {
    const mensajes = {
        'sin-preguntas': 'No se encontraron preguntas válidas en el PDF',
        'formato-invalido': errorMsg || 'El formato del PDF no es reconocido',
        'no-preguntas': 'No se encontraron preguntas en el documento',
        'no-preguntas-indicadores': 'No se encontraron preguntas en los indicadores',
        'procesamiento': errorMsg || 'Error al procesar el archivo',
        'fatal': errorMsg || 'Ocurrió un error inesperado al procesar el PDF',
        'default': errorMsg || 'Ha ocurrido un error'
    }
    
    const mensaje = mensajes[errorType] || mensajes['default']
    showAlert(mensaje, 7000, 'error')
}

const loader = document.getElementById("loader")
document.getElementById('formulario').addEventListener('submit', function(e) {
    loader.classList.remove('hidden')
    loader.classList.add('flex')
});

