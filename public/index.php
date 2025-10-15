<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Converter Document</title>
    <link rel="stylesheet" href="public/style.css">
</head>
<body>
<div class="parent">
    <div class="header">
    <h1>Convertidor PDF a XML para Moodle</h1>
    </div>
    <div class="form-container">
        <form action="/upload" method="post" enctype="multipart/form-data">
            <div class="file-input">
                <span class="file-button">
                    <svg width="60" height="60" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M5 17C5 18.1046 5.89543 19 7 19L17 19C18.1046 19 19 18.1046 19 17L19 16C19 15.4477 19.4477 15 20 15C20.5523 15 21 15.4477 21 16L21 17C21 19.2091 19.2091 21 17 21L7 21C4.79086 21 3 19.2091 3 17L3 16C3 15.4477 3.44771 15 4 15C4.55228 15 5 15.4477 5 16L5 17ZM7.29289 8.70711C6.90237 8.31658 6.90237 7.68342 7.29289 7.29289L11.2929 3.29289C11.4804 3.10536 11.7348 3 12 3C12.2652 3 12.5196 3.10536 12.7071 3.29289L16.7071 7.29289C17.0976 7.68342 17.0976 8.31658 16.7071 8.70711C16.3166 9.09763 15.6834 9.09763 15.2929 8.70711L13 6.41421L13 16C13 16.5523 12.5523 17 12 17C11.4477 17 11 16.5523 11 16L11 6.41421L8.70711 8.70711C8.31658 9.09763 7.68342 9.09763 7.29289 8.70711Z" fill="#687782"/>
</svg>Agregar archivo
                </span>
                <input type="file" name="documentFile" id="document-file" accept=".pdf">
                
            </div>
            <button class="btn-submit"> <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M4 3C4.55228 3 5 3.44772 5 4V6.34298C6.64938 4.30446 9.17168 3 12 3C16.5903 3 20.3767 6.43564 20.9304 10.8763C20.9988 11.4243 20.6099 11.924 20.0618 11.9923C19.5138 12.0607 19.0141 11.6718 18.9458 11.1237C18.5153 7.67174 15.5689 5 12 5C9.62231 5 7.51998 6.18566 6.25442 8H9C9.55228 8 10 8.44772 10 9C10 9.55228 9.55228 10 9 10H4C3.44772 10 3 9.55228 3 9V4C3 3.44772 3.44772 3 4 3ZM3.93815 12.0077C4.48619 11.9393 4.98587 12.3282 5.05421 12.8763C5.48467 16.3283 8.43109 19 12 19C14.3777 19 16.48 17.8143 17.7456 16H15C14.4477 16 14 15.5523 14 15C14 14.4477 14.4477 14 15 14H20C20.5523 14 21 14.4477 21 15V20C21 20.5523 20.5523 21 20 21C19.4477 21 19 20.5523 19 20V17.657C17.3506 19.6955 14.8283 21 12 21C7.40967 21 3.62332 17.5644 3.06958 13.1237C3.00124 12.5757 3.39011 12.076 3.93815 12.0077Z" fill="white"/>
</svg>
Convertir</button>
        </form>
    </div>
    <div class="download-container">
        <?php if (isset($_GET['success'])): ?>
            <a href="/download" class="download"> <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M5 17C5 18.1046 5.89543 19 7 19L17 19C18.1046 19 19 18.1046 19 17L19 16C19 15.4477 19.4477 15 20 15C20.5523 15 21 15.4477 21 16L21 17C21 19.2091 19.2091 21 17 21L7 21C4.79086 21 3 19.2091 3 17L3 16C3 15.4477 3.44771 15 4 15C4.55228 15 5 15.4477 5 16L5 17ZM7.29289 11.2929C7.68342 10.9024 8.31658 10.9024 8.70711 11.2929L11 13.5858L11 4C11 3.44772 11.4477 3 12 3C12.5523 3 13 3.44772 13 4L13 13.5858L15.2929 11.2929C15.6834 10.9024 16.3166 10.9024 16.7071 11.2929C17.0976 11.6834 17.0976 12.3166 16.7071 12.7071L12.7071 16.7071C12.3166 17.0976 11.6834 17.0976 11.2929 16.7071L7.29289 12.7071C6.90237 12.3166 6.90237 11.6834 7.29289 11.2929Z" fill="#FF8D28"/>
</svg> Descargar archivo</a>
        <?php endif; ?>
       
    </div>
    <div class="footer">
        <p>VRA - Gestión de aula <?php echo date("Y") ?></p>
    </div>
    <div id="alert"></div>
</div>
<script src="public/app.js"></script>
</body>
</html>