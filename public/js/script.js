
// Secciones Desplegables
function toggleSection(sectionId) {
    const section = document.getElementById(sectionId);
    section.style.display = section.style.display === 'block' ? 'none' : 'block';
}

// Testimonios - Cambiar automáticamente
document.addEventListener('DOMContentLoaded', function() {
    const testimonies = [
        { text: '"Cambiar mis hábitos alimenticios me ha hecho sentir con más energía y vitalidad."', author: 'Ana Pérez' },
        { text: '"Hacer ejercicio regularmente ha transformado mi cuerpo y mi mente."', author: 'Carlos Gómez' },
        { text: '"La meditación me ha dado claridad mental y paz interior."', author: 'Laura Fernández' },
        { text: '"Dormir mejor y cuidar mi alimentación ha mejorado mi productividad cada día."', author: 'Marcos Díaz' },
        { text: '"Gracias a una rutina constante de ejercicios, me siento más fuerte y saludable."', author: 'Valeria Ruiz' },
        { text: '"Nunca imaginé que pequeños cambios diarios tuvieran un impacto tan grande."', author: 'Julián Torres' },
        { text: '"Con la guía adecuada, he aprendido a escuchar lo que mi cuerpo necesita."', author: 'Sofía Morales' },
        { text: '"Reducir el estrés con técnicas de respiración ha sido un antes y después para mí."', author: 'Natalia Herrera' },
        { text: '"La combinación de buena nutrición y actividad física me devolvió la confianza."', author: 'Pedro Ramírez' },
        { text: '"Estar saludable no es solo físico, también es sentirme bien conmigo mismo."', author: 'Lucía Méndez' }
    ];

    let currentTestimonyIndex = 0;
    const testimonySlider = document.getElementById('testimony-slider');

    if (testimonySlider) {
        function updateTestimony() {
            const testimony = testimonies[currentTestimonyIndex];
            testimonySlider.querySelector('.testimony-text').textContent = testimony.text;
            testimonySlider.querySelector('.testimony-author').textContent = testimony.author;
            currentTestimonyIndex = (currentTestimonyIndex + 1) % testimonies.length;
        }

        updateTestimony();
        setInterval(updateTestimony, 5000); // Cambiar cada 5 segundos
    }
});