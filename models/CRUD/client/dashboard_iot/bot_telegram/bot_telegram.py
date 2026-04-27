from sqlalchemy import Column, String, Integer, BigInteger, ForeignKey
from sqlalchemy.ext.declarative import declarative_base

Base = declarative_base()

class BotTelegramModel(Base):
    __tablename__= "bot_telegram"

    Id_bot = Column(Integer, primary_key=True, autoincrement=True)

    User_id = Column(Integer, ForeignKey('reservation_parc.User_id'), nullable=False)
    Token_bot = Column(String(120), nullable=False)
    Chat_id_bot = Column(BigInteger, nullable=False)

    def __init__(self, user_id):
        self.User_id = user_id