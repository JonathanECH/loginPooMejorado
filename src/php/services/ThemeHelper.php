<?php
// src/php/services/ThemeHelper.php
require_once __DIR__ . '/../models/SettingsModel.php';

class ThemeHelper
{
    public static function renderThemeStyles($connection)
    {
        $model = new SettingsModel($connection);
        $colors = $model->getThemeColors();
        
        // Aseguramos que existan valores por defecto si la DB falla
        $primary = $colors['primary_color'] ?? '#007bff';
        $secondary = $colors['secondary_color'] ?? '#2c3e50';
        $text = $colors['text_color'] ?? '#333333';
        $bg = $colors['bg_color'] ?? '#f8f9fa';
        $card = $colors['card_color'] ?? '#ffffff';

        echo "<style>
            :root {
                --primary-color: {$primary} !important;
                --secondary-color: {$secondary} !important;
                --text-color: {$text} !important;
                --bg-color: {$bg} !important;
                --card-color: {$card} !important;
            }

            /* 1. FONDO GLOBAL */
            body { 
                background-color: var(--bg-color) !important; 
                color: var(--text-color) !important; 
            }

            /* 2. SIDEBAR */
            .sidebar { background-color: var(--secondary-color) !important; }
            .sidebar h3 { color: #fff !important; }

            /* 3. BOTONES */
            .btn-primary, .btn-save, .submit-btn, .btn-action.btn-primary { 
                background-color: var(--primary-color) !important; 
                border-color: var(--primary-color) !important;
                color: #fff !important;
            }

            /* 4. TÍTULOS (Ahora usan el color secundario o texto según prefieras) */
            h1, h2, h3, h4, 
            .productos-title, 
            .faq-title, 
            .container-form__title, 
            #testimonio-pepon { 
                color: var(--secondary-color) !important; 
            }

            /* ================================================= */
            /* 5. APLICAR COLOR A LOS CONTENEDORES (TARJETAS)    */
            /* ================================================= */

            /* Productos (figure) */
            #productos figure {
                background-color: var(--card-color) !important;
                border: 1px solid rgba(0,0,0,0.05); /* Borde sutil opcional */
            }
            /* Texto dentro de productos (para que se lea si el fondo es oscuro) */
            #productos figure figcaption, 
            #productos figure p {
                color: var(--text-color) !important;
            }

            /* Testimonios */
            .testimonial-card {
                background-color: var(--card-color) !important;
            }
            .testimonial-card blockquote, 
            .testimonial-card .author-name, 
            .testimonial-card .author-role {
                color: var(--text-color) !important;
            }

            /* Preguntas Frecuentes */
            .pregunta-card {
                background-color: var(--card-color) !important;
            }
            .pregunta-card h4, 
            .pregunta-card p {
                color: var(--text-color) !important;
            }

            /* Formulario de Contacto (La caja blanca contenedora) */
            .container-form__form {
                background-color: var(--card-color) !important;
            }
            /* Los inputs dentro del formulario */
            .container-form__form label {
                color: var(--text-color) !important;
            }

            /* Dropdown del Carrito */
            .shein-dropdown {
                background-color: var(--card-color) !important;
            }
            .shein-name, .shein-price {
                color: var(--text-color) !important;
            }

            /* Panel de Usuario / Login / Registro */
            .profile-card, 
            .form-container { 
                background-color: var(--card-color) !important; 
            }

        </style>";
    }
}