        </div>
    </main>
    <script>
        lucide.createIcons();

        // Media Picker Logic
        let currentTargetInput = null;

        function openMediaModal(inputId) {
            currentTargetInput = document.getElementById(inputId);
            document.getElementById('mediaModal').style.display = 'block';
            loadMediaForPicker();
        }

        function closeMediaModal() {
            document.getElementById('mediaModal').style.display = 'none';
        }

        async function loadMediaForPicker() {
            const gallery = document.getElementById('mediaGallery');
            gallery.innerHTML = '<p style="grid-column: 1/-1; text-align: center;">Loading...</p>';
            
            try {
                const response = await fetch('get_media_json.php');
                const media = await response.json();
                
                gallery.innerHTML = '';
                media.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'media-picker-item';
                    div.innerHTML = `<img src="../${item.file_path}" alt="${item.file_name}" data-path="${item.file_path}">`;
                    div.onclick = function() {
                        document.querySelectorAll('.media-picker-item').forEach(el => el.classList.remove('selected'));
                        this.classList.add('selected');
                    };
                    gallery.appendChild(div);
                });
            } catch (error) {
                gallery.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: red;">Error loading media.</p>';
            }
        }

        document.getElementById('confirmMediaBtn').onclick = function() {
            const selected = document.querySelector('.media-picker-item.selected img');
            if (selected && currentTargetInput) {
                currentTargetInput.value = selected.getAttribute('data-path');
                closeMediaModal();
                // Trigger preview if exists
                const previewId = currentTargetInput.id + '_preview';
                const previewImg = document.getElementById(previewId);
                if (previewImg) {
                    previewImg.src = '../' + selected.getAttribute('data-path');
                    previewImg.style.display = 'block';
                }
            }
        };

        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('mediaModal');
            if (event.target == modal) closeMediaModal();
        };
    </script>
</body>
</html>
