 // --- 1. KAHVELER / KUPALAR SEKMESİ ---
        function switchTab(tabName) {
            const btnKahveler = document.getElementById('btnKahveler');
            const btnKupalar = document.getElementById('btnKupalar');
            const gridKahveler = document.getElementById('kahveler-grid');
            const gridKupalar = document.getElementById('kupalar-grid');

            if (tabName === 'kahveler') {
                btnKahveler.className = 'toggle-btn btn-active';
                btnKupalar.className = 'toggle-btn btn-inactive';
                gridKahveler.style.display = 'grid'; 
                gridKupalar.style.display = 'none';  
            } else {
                btnKupalar.className = 'toggle-btn btn-active';
                btnKahveler.className = 'toggle-btn btn-inactive';
                gridKahveler.style.display = 'none'; 
                gridKupalar.style.display = 'grid';  
            }
        }

        // --- 2. SLIDER (KAYAN RESİMLER) KODLARI ---
        let currentSlide = 0;
        const totalSlides = 4;
        const sliderTrack = document.getElementById('sliderTrack');
        const indicators = document.querySelectorAll('.indicator');

        function updateSlider() {
            sliderTrack.style.transform = `translateX(-${currentSlide * 25}%)`;
            indicators.forEach((ind, index) => {
                if(index === currentSlide) {
                    ind.classList.add('active');
                } else {
                    ind.classList.remove('active');
                }
            });
        }

        function goToSlide(index) {
            currentSlide = index;
            updateSlider();
        }

        setInterval(() => {
            currentSlide = (currentSlide + 1) % totalSlides;
            updateSlider();
        }, 4000);