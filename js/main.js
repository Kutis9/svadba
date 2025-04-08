document.addEventListener('DOMContentLoaded', function() {
    // Tlačidlo na zobrazenie/skrytie formulára
    const showFormButton = document.getElementById('show-upload-form');
    const formContainer = document.getElementById('upload-form-container');
    const uploadForm = document.getElementById('upload-form');
    const photosContainer = document.getElementById('photos-container');
    
    // Zobraziť/skryť formulár na upload fotiek
    showFormButton.addEventListener('click', function() {
        if (formContainer.style.display === 'none') {
            formContainer.style.display = 'block';
            showFormButton.textContent = 'Skryť formulár';
        } else {
            formContainer.style.display = 'none';
            showFormButton.textContent = 'Pridať fotky';
        }
    });
    
    // Spracovanie uploadu fotiek pomocou AJAX
    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Zobrazenie indikátora nahrávania
        const uploadButton = document.getElementById('upload-button');
        const originalButtonText = uploadButton.textContent;
        uploadButton.textContent = 'Nahrávam...';
        uploadButton.disabled = true;
        
        // Získanie údajov z formulára
        const formData = new FormData(uploadForm);
        
        // Odoslanie požiadavky na server
        fetch('upload.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Obnovenie tlačidla
            uploadButton.textContent = originalButtonText;
            uploadButton.disabled = false;
            
            // Zobrazenie správy o výsledku
            alert(data.message);
            
            if (data.success) {
                // Vyčistenie formulára
                uploadForm.reset();
                
                // Skrytie formulára
                formContainer.style.display = 'none';
                showFormButton.textContent = 'Pridať fotky';
                
                // Obnovenie galérie - jednoduchý spôsob je načítať stránku znova
                location.reload();
            }
        })
        .catch(error => {
            console.error('Chyba pri nahrávaní:', error);
            uploadButton.textContent = originalButtonText;
            uploadButton.disabled = false;
            alert('Nastala chyba pri nahrávaní fotiek. Skúste to prosím znova.');
        });
    });
    
    // Zväčšenie fotky po kliknutí
    photosContainer.addEventListener('click', function(e) {
        const clickedItem = e.target.closest('.photo-item');
        if (clickedItem && e.target.tagName === 'IMG') {
            const img = e.target;
            const imgSrc = img.src;
            
            // Vytvorenie modálneho okna
            const modal = document.createElement('div');
            modal.classList.add('photo-modal');
            modal.innerHTML = `
                <div class="modal-content">
                    <span class="close-modal">&times;</span>
                    <img src="${imgSrc}" alt="Zväčšená fotka">
                </div>
            `;
            
            // Pridanie štýlov pre modálne okno
            modal.style.position = 'fixed';
            modal.style.top = '0';
            modal.style.left = '0';
            modal.style.width = '100%';
            modal.style.height = '100%';
            modal.style.backgroundColor = 'rgba(0, 0, 0, 0.9)';
            modal.style.display = 'flex';
            modal.style.alignItems = 'center';
            modal.style.justifyContent = 'center';
            modal.style.zIndex = '1000';
            
            const modalContent = modal.querySelector('.modal-content');
            modalContent.style.position = 'relative';
            modalContent.style.maxWidth = '90%';
            modalContent.style.maxHeight = '90%';
            
            const modalImg = modal.querySelector('img');
            modalImg.style.maxWidth = '100%';
            modalImg.style.maxHeight = '90vh';
            modalImg.style.display = 'block';
            
            const closeBtn = modal.querySelector('.close-modal');
            closeBtn.style.position = 'absolute';
            closeBtn.style.top = '10px';
            closeBtn.style.right = '20px';
            closeBtn.style.color = 'white';
            closeBtn.style.fontSize = '35px';
            closeBtn.style.fontWeight = 'bold';
            closeBtn.style.cursor = 'pointer';
            
            // Pridanie modálneho okna do dokumentu
            document.body.appendChild(modal);
            
            // Zatvorenie modálneho okna
            closeBtn.onclick = function() {
                document.body.removeChild(modal);
            };
            
            // Zatvorenie modálneho okna kliknutím mimo obrázka
            modal.onclick = function(event) {
                if (event.target === modal) {
                    document.body.removeChild(modal);
                }
            };
        }
    });
});