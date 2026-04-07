from sqlalchemy import Column, String, Integer, DECIMAL, DateTime, func, ForeignKey
from sqlalchemy.ext.declarative import declarative_base

# Création d'une variable pour stocker la classe parent créer à partir de la fonction "declarative_base" qui sera importer
# à partir de la bibliothèque sqlalchemy
Base = declarative_base()

class DonneeCapteurModel(Base):
    __tablename__= "donnee_capteur"
    
    # Doit obligatoirement être présent dans le model. Même dans le cas, où il ne serait jamais utilisé.
    # Nécessaire pour identifier la structure des champs de donnée de ma table stocké en base de donnée
    Id_data_capteur = Column(Integer, primary_key=True, autoincrement=True)

    Id_parc = Column(String(10), ForeignKey('info_parc.Id_parc'), nullable=False)
    Humidite = Column(DECIMAL(5, 2), nullable=True)
    Temperature = Column(DECIMAL(4, 2), nullable=True)
    Luminosite = Column(DECIMAL(10, 2), nullable=True)
    Type_alerte = Column(String(50), nullable=False, default="inconnu")
    Message = Column(String(255), nullable=False, default="-- // -- // --")

    # Valeur par défaut gérée par la base de donnée. Donc, pas besoin de l'initialiser au niveau du constructeur de la classe
    Date_pub = Column(DateTime, server_default=func.current_timestamp())

    def __init__(self, id_parc, humidite, temperature, luminosite, type_alerte, message):
        self.Id_parc = id_parc
        self.Humidite = humidite
        self.Temperature = temperature
        self.Luminosite = luminosite
        self.Type_alerte = type_alerte
        self.Message = message