-- Création de la base de données
CREATE DATABASE IF NOT EXISTS gestion_participatif;
USE gestion_participatif;

-- Table `users`
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Nom VARCHAR(255) NOT NULL,
    Prenom VARCHAR(255) NOT NULL,
    Num_tel VARCHAR(15),
    Date_Naissance DATE,
    Email VARCHAR(255) UNIQUE NOT NULL,
    Mot_de_Passe VARCHAR(255) NOT NULL
);

-- Table `reservation_parc`
CREATE TABLE IF NOT EXISTS reservation_parc (
    id INT AUTO_INCREMENT PRIMARY KEY,
    User_id INT NOT NULL,
    Id_parc INT NOT NULL,
    Duree_res INT NOT NULL,
    Date_res DATE NOT NULL,
    Date_fin DATE,
    Date_limite DATE,
    FOREIGN KEY (User_id) REFERENCES users(id)
);

-- Table `like_avis`
CREATE TABLE IF NOT EXISTS like_avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    User_id INT NOT NULL,
    Id_parc INT NOT NULL,
    Id_avis INT NOT NULL,
    Type_action ENUM('like', 'dislike') NOT NULL,
    FOREIGN KEY (User_id) REFERENCES users(id)
);

-- Table `avis`
CREATE TABLE IF NOT EXISTS avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    User_id INT NOT NULL,
    Id_parc INT NOT NULL,
    Avis TEXT NOT NULL,
    FOREIGN KEY (User_id) REFERENCES users(id)
);

-- Table `info_parc`
CREATE TABLE IF NOT EXISTS info_parc (
    Id_parc INT AUTO_INCREMENT PRIMARY KEY,
    Taille_parc FLOAT NOT NULL,
    Nom_parc VARCHAR(255) NOT NULL,
    Prix_parc DECIMAL(10, 2) NOT NULL,
    Status_parc VARCHAR(10) NOT NULL,
    Exposition VARCHAR(255),
    Equipements TEXT,
    Preferences TEXT,
    Description TEXT,
    Chemin_image VARCHAR(255)
);