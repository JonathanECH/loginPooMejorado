<?php
// src/php/models/SettingsModel.php
require_once 'DbModel.php';

class SettingsModel extends DbModel
{
    public function __construct(mysqli $connection)
    {
        parent::__construct($connection);
    }

    /**
     * Obtiene los colores actuales (incluyendo card_color).
     */
    public function getThemeColors(): array
    {
        // SQL corregido para traer el 5to color
        $sql = "SELECT primary_color, secondary_color, text_color, bg_color, card_color FROM site_settings WHERE id = 1";
        $result = $this->runSelectStatement($sql, "");
        
        if (is_string($result) || $result->num_rows === 0) {
            // Valores por defecto si no hay datos
            return [
                'primary_color' => '#007bff',
                'secondary_color' => '#2c3e50',
                'text_color' => '#333333',
                'bg_color' => '#f8f9fa',
                'card_color' => '#ffffff' 
            ];
        }
        
        return $result->fetch_assoc();
    }

    /**
     * Actualiza los 5 colores en la base de datos.
     */
    public function updateThemeColors($primary, $secondary, $text, $bg, $card): bool|string
    {
        // SQL corregido con 5 parámetros (?)
        $sql = "UPDATE site_settings SET primary_color = ?, secondary_color = ?, text_color = ?, bg_color = ?, card_color = ? WHERE id = 1";
        
        // "sssss" indica que pasamos 5 strings
        $result = $this->runDmlStatement($sql, "sssss", $primary, $secondary, $text, $bg, $card);
        
        if (is_string($result)) {
            return "Error al guardar colores: " . $result;
        }
        
        return true;
    }
}