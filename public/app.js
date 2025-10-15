document.getElementById('document-file').addEventListener('change', function() {
    const fileName = this.files[0] ? this.files[0].name : 'Ningún archivo seleccionado';
    document.querySelector('.file-button').textContent = fileName;
})

setTimeout(()=>{
    window.history.replaceState({}, document.title, window.location.pathname);
},7000)
const url = new URLSearchParams(window.location.search)
const showAlert = (message, duration, color, background) =>{
    const alertBox = document.getElementById('alert');
    alertBox.textContent = message;
    alertBox.classList.add('active');
    alertBox.style.color = color
    alertBox.style.background = background
    
    setTimeout(() => {
        alertBox.classList.remove('active');
    }, duration);
}

if(url.has('error')){
    showAlert("No ha seleccionado ningún archivo", 7000, "rgba(214, 38, 7, 1)", "rgba(245, 6, 6, 0.17)");
}
if(url.has('success')){
    showAlert("¡Archivo horneado y servido! Descárgalo cuando quieras", 7000, "rgba(4, 53, 7, 1)", "rgba(10, 151, 20, 0.39)");
}
if(url.has('error-file')){
    showAlert("Solo aceptamos archivos PDF por ahora. ¡Gracias!", 7000, "rgba(77, 90, 4, 1)", "rgba(157, 184, 1, 0.69)");
}