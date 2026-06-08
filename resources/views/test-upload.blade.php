<!DOCTYPE html>
<html>
<head>
    <title>Test Image Upload</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Test Image Upload</h1>
    
    <form id="upload-form">
        <input type="file" id="image-input" accept="image/*">
        <button type="button" onclick="uploadImage()">Upload</button>
    </form>
    
    <div id="result"></div>
    <div id="preview"></div>
    
    <script>
    function uploadImage() {
        const fileInput = document.getElementById('image-input');
        const file = fileInput.files[0];
        
        if (!file) {
            alert('Vui lòng chọn file');
            return;
        }
        
        const formData = new FormData();
        formData.append('image', file);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        fetch('/admin/upload-image', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('result').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            
            if (data.success) {
                document.getElementById('preview').innerHTML = '<img src="' + data.url + '" style="max-width: 300px;">';
            }
        })
        .catch(error => {
            document.getElementById('result').innerHTML = '<p style="color: red;">Error: ' + error.message + '</p>';
        });
    }
    </script>
</body>
</html>
