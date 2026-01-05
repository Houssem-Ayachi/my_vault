-- Table des Utilisateurs (Pour se connecter à l'appli)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table des Secrets (Le contenu du coffre)
CREATE TABLE secrets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100),
    login VARCHAR(100),
    -- On prévoit large pour le mot de passe car il sera chiffré (texte long)
    encrypted_password TEXT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);