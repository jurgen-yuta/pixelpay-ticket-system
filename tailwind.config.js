/** @type {import('tailwindcss').Config} */
module.exports = {
    // Aquí se define qué archivos debe escanear Tailwind para encontrar las clases CSS.
    content: [
        './resources/**/*.blade.php', // Archivos Blade (plantillas base)
        './resources/**/*.js',        // Archivos JavaScript
        './resources/**/*.vue',       // Componentes Vue, donde están la mayoría de nuestras clases
    ],

    theme: {
        extend: {
            // Se puede extender la paleta de colores, tipografía, etc.
            fontFamily: {
                sans: ['Inter', 'sans-serif'], // Usamos Inter como la fuente principal
            },
        },
    },

    plugins: [
        // Plugins recomendados para formularios (como el estilo de los inputs y selects)
        require('@tailwindcss/forms'),
    ],
};