from sqlalchemy import Column, String, Integer, ForeignKey
from sqlalchemy.ext.declarative import declarative_base

Base = declarative_base()

class SeuilCapteurModel(Base):
    __tablename__= "seuillage"

    Id_seuil = Column(Integer, primary_key=True, autoincrement=True)

    Id_parc = Column(String(10), ForeignKey('info_parc.Id_parc'), nullable=False)
    Temp_seuil = Column(Integer, nullable=True)
    Humidite_seuil = Column(Integer, nullable=True)

    def __init__(self, id_parc, temp_seuil, humidite_seuil):
        self.Id_parc = id_parc
        self.Temp_seuil = temp_seuil
        self.Humidite_seuil = humidite_seuil