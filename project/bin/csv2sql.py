# -*- coding: iso-8859-1 -*
import sys, pandas as pd
from sqlalchemy import create_engine
engine = create_engine('sqlite:///'+sys.argv[1], echo=False, encoding='iso-8859-1')


csv = pd.read_csv("export_bi_contrats.csv", encoding='iso-8859-1', delimiter=";", index_col=False).rename(columns={
       "#CONTRA": "type document", 'type de vente (VIN_VRAC, VIN_BOUTEILLE, RAISIN, MOUT)': 'type de vente', 'volume propose (en hl)': 'volume propose', 'volume enleve (en hl)': "volume enleve", 'prix unitaire (en hl)' : 'prix unitaire',
       'prix unitaire definitif (en hl)': 'prix unitaire definitif', 'prix variable (OUI, NON)': 'prix variable',
       'contrat interne (OUI, NON)': 'contrat interne', 'original (OUI, NON)' : 'original',
       'type de contrat(SPOT, PLURIANNUEL)' : "type de contrat",'type de produit (GENERIQUE, DOMAINE)': 'type de produit', 'nature de la cvo (MARCHE_DEFINITIF, COMPENSATION, NON_FINANCIERE, VINAIGRERIE)': 'nature de la cvo'})
csv.to_sql('contrat', con=engine, if_exists='replace')

csv = pd.read_csv("export_bi_drm.csv", encoding='iso-8859-1', delimiter=";", index_col=False).rename(columns={"#DRM ID": "DRM ID", 'numéro archivage': 'numero archivage'})
csv.to_sql('drm', con=engine, if_exists='replace')

csv = pd.read_csv("export_bi_mouvements.csv", encoding='iso-8859-1', delimiter=";", index_col=False).rename(columns={'pays export (si export)': 'pays export', '#MOUVEMENT': "type de document"})
csv.to_sql('mouvement', con=engine, if_exists='replace')

csv = pd.read_csv("export_bi_etablissements.csv", encoding='iso-8859-1', delimiter=";", index_col=False).rename(columns={ 'statut (ACTIF, SUSPENDU)': 'statut', "#ETABLISSEMENT": "type de document"})
csv.to_sql('etablissement', con=engine, if_exists='replace')

csv = pd.read_csv("export_bi_societes.csv", encoding='iso-8859-1', delimiter=";", index_col=False).rename(columns={ 'statut (ACTIF, SUSPENDU)': 'statut', "#SOCIETE": "type de document"})
csv.to_sql('societe', con=engine, if_exists='replace')

csv = pd.read_csv("export_bi_dss.csv", encoding='iso-8859-1', delimiter=";", index_col=False).rename(columns={ 'statut (ACTIF, SUSPENDU)': 'statut', "#DS": "type de document"})
csv.to_sql('ds', con=engine, if_exists='replace')

