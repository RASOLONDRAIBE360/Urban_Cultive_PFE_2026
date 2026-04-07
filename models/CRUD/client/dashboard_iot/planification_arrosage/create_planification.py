from sqlalchemy import Column, String, Date, Time, Integer, ForeignKey
from sqlalchemy.ext.declarative import declarative_base

# Création d'une variable pour stocker la classe parent créer à partir de la fonction "declarative_base" qui sera importer
# à partir de la bibliothèque sqlalchemy
Base = declarative_base()

# Ici je fais le mapping. Etant donné que j'ai déjà créé la table "planification_arrosage" dans ma base de donnée
class PlanificationModel(Base):
    # Le nom de la table ici doit obligatoirement correspondre à celui que j'ai mis dans ma base de donnée pour éviter que SQLAlchemy me
    # génère une erreur 
    __tablename__ = "planification_arrosage"

    # Nom des attributs suivant doivent correspondrent exactement avec le nom des champs qui se trouvent réellement dans ma table qui est stocké dans 
    # ma base de donnée
    Id_planning = Column(String(45), primary_key=True, nullable=False)
    Id_parc = Column(String(10), ForeignKey("info_parc.Id_parc"), nullable=False)
    Duree_arrosage = Column(Integer, nullable=False)
    Date_arrosage = Column(Date, nullable=False)
    Heure_arrosage = Column(Time, nullable=False)

    # Celui-là est une constructeur pour me permettra d'initialiser la valeur des champs de ma table  
    def __init__(self, id_planning, id_parc, duree_arrosage, date_arrosage, heure_arrosage):
        self.Id_planning = id_planning
        self.Id_parc = id_parc
        self.Duree_arrosage = duree_arrosage
        self.Date_arrosage = date_arrosage
        self.Heure_arrosage = heure_arrosage
