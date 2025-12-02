<?php

// 2. MODELO BASE (DbModel debe cargarse ANTES que cualquier clase hija)
require_once __DIR__ . '/models/DbModel.php';

// 3. MODELOS HIJOS
require_once __DIR__ . '/models/UserModel.php';
require_once __DIR__ . '/models/ProductModel.php';
require_once __DIR__ . '/models/CartModel.php';

// 4. SERVICIOS
require_once __DIR__ . '/services/UserValidator.php'; 