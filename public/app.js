document.getElementById('document-file').addEventListener('change', function() {

const fileName = this.files[0] ? this.files[0].name : 'Ningún archivo seleccionado';
    document.querySelector('.file-button').textContent = fileName;
    })
setTimeout(()=>{
        window.history.replaceState({}, document.title, window.location.pathname);
    },9000)

const url = new URLSearchParams(window.location.search)
if(url.has('error')){
    alert('No se ha seleccionado ningún archivo. Por favor, seleccione un archivo e intente de nuevo.');
}