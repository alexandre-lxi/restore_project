#!/bin/bash

CURRENT_VER=1.1

# initialisation des options

TERMINAL="FAUX"
AIDE="FAUX"
VERSION="FAUX"
RECURSIF="FAUX"

# Définition des fonctions du script

# Test du code retour de zenity :
function testcrd()
{
	if [ ${crd} = -1 ]; then
		zenity --error --title "Dossier Magique" --text "Une erreur inattendue s'est produite. Abandon."
		exit 2
	fi
	if [ ${crd} = 1 ]; then
		zenity --info --title "Dossier Magique" --text "Vous avez choisi d'annuler le script. Sortie."
		exit 1
	fi
	return 0
}

# Déplacement d'un fichier et mise à jour du fichier log :
function bouge()
{
    mv "${1}" "${2}"
    heure=`date +%D-%H:%m`
    echo "[${heure}] "${1}" déplacé dans "${2}""
# >> ${LOG}
    return 0
}

# Créons les répertoires s'ils n'existent pas :
function createdirs()
{
    mkdir -p "${TXT}"
    mkdir -p "${PDF}"
    mkdir -p "${AUDIO}"
    mkdir -p "${VIDEO}"
    mkdir -p "${IMG}"
    mkdir -p "${ARCHIVES}"
    mkdir -p "${DOCS}"
    mkdir -p "${TEX}"
    mkdir -p "${MISC}"
    mkdir -p "${BIN}"

    return 0
}

# Trions les fichiers :
function tri()
{
    cd "${1}"
	# Faut-il gérer la récursivité pour les sources ?
	if [[ "${RECURSIF}" = "VRAI" && "${DIR}" != "${1}" ]]
	then
		crd=0
		while [ ${crd} = 0 ]
		do
			ls -d */ 2> /dev/null > /tmp/tri
			crd=$?
			while read dossier
			do
				# On remonte tout le dossier d'un niveau
				mv -t "./" "${dossier}"* 2> /dev/null
				# Puis on supprime le dossier vide
				rmdir "${dossier}"
			done < /tmp/tri
		done
	fi
    ls > /tmp/tri
    while read fichier
    do
		# Cas particulier des fichiers à traiter d'après l'extension
		type="${fichier##*.}"
		case "${type}" in
			wma) bouge "${fichier}" "${AUDIO}";;

			*)	# Utilisons si possible le type mime :
				type=`file -bi "${fichier}"`

		        case "${type}" in
		            *script*) bouge "${fichier}" "${BIN}";;
            
		            *executable*) bouge "${fichier}" "${BIN}";;
        
		            *pdf* | *dvi* | *postscript*) bouge "${fichier}" "${PDF}";;
            
		            *audio* | *ogg*) bouge "${fichier}" "${AUDIO}";;
        
		            *video* | *flash*) bouge "${fichier}" "${VIDEO}";;
    
		            *image*) bouge "${fichier}" "${IMG}";;

		            *tar* | *rar* | *zip*) bouge "${fichier}" "${ARCHIVES}";;

		            *msword* | *excel* | *powerpoint* | *rtf* | *opendocument*) bouge "${fichier}" "${DOCS}";;

					*)	# Si le type mime ne suffit pas :
				        type=`file -b "${fichier}"`

		        		case "${type}" in
		        		    *directory*) continue;;
               
		        		    *byte-compiled*) bouge "${fichier}" "${BIN}";;

				            *script*) bouge "${fichier}" "${BIN}";;
               
		        		    *LaTeX*) bouge "${fichier}" "${TEX}";;
        
		        		    *ASF*) bouge "${fichier}" "${VIDEO}";;

				            *text*) bouge "${fichier}" "${TXT}";;
    
							*)	# Le type est donc inconnu :
								bouge "${fichier}" "${MISC}";;
		        		esac
						;;
				esac
				;;
		esac
    
    done < /tmp/tri

    return 0
}

# Testons d'abord si le script est lancé en mode terminal
while getopts ":agrtv-:" OPT
do
    # gestion des options longues avec ou sans argument
    [ $OPT = "-" ] && case "${OPTARG%%=*}" in
        aide) OPT="a" ;;
        graphique) OPT="g" ;;
		recursif) OPT="r";;
        terminal) OPT="t" ;;
		recursif) OPT="r";;
        version) OPT="v" ;;
        *) echo "Option inconnue" ; exit 1 ;;
    esac
    # puis gestion des options courtes
    case $OPT in
        a) AIDE="VRAI" ;;
        g) TERMINAL="FAUX" ;;
		r) RECURSIF="VRAI";;
        t) TERMINAL="VRAI" ;;
		r) RECURSIF="VRAI";;
        v) VERSION="VRAI" ;;
        *) echo "Option inconnue" ; exit 1 ;;
    esac
done 

# Aide

if [ "$AIDE" = "VRAI" ]
then
	echo "Syntaxe 1 : avec 0 ou 1 option et sans paramètre."
	echo "            $0					Mode graphique."
	echo "            $0 -g | --graphique	Mode graphique."
	echo "            $0 -a | --aide		Affiche l'aide."
	echo "            $0 -v | --version		Affiche la version."
	echo "            $0 -r | --recursif    Gére la récursivité."
    echo "Syntaxe 2 : en mode terminal avec paramètre(s) obligatoire(s)."
	echo "            $0 -t | --terminal CIBLE [SOURCE1 ... SOURCEn]"
	echo "            où CIBLE est le dossier résultant classé"
	echo "            et SOURCE(s) le(s) dossier(s) vrac à trier."
	echo "            Si SOURCE est omis, alors CIBLE=SOURCE."
    exit 0
fi

# Version

if [ "$VERSION" = "VRAI" ]
then
    echo " "
    echo "Version $0 : $CURRENT_VER"
#    head -15 $0 | grep -v bash
    exit 0
fi

# Mémorisons le répertoire courant
OLDDIR=`pwd`
# Initialisons le dossier racine CIBLE
DIR="${HOME}"
# Définissons le fichier de log (aucun par défaut)
LOG="/dev/null"

# Option "terminal" pour utilisation non graphique
if [ "$TERMINAL" = "VRAI" ]
then
	# On élimine les options pour charger le(s) paramètre(s)
	while [ "${1:0:1}" = "-" ]
	do
		shift
	done
	if [ "${1}" = "" ]
	then
		echo "En mode terminal, indiquer obligatoirement le(s) paramètre(s)"
		exit 1
	fi
# Sinon, exécution en mode graphique
else
	# On affiche d'abord une fenêtre d'aide à l'utilisateur
	echo "- Vous allez tout d'abord choisir le dossier dans lequel seront créés" > /tmp/notice
	echo "  les sous-dossiers où classer les fichiers triés. C'est le dossier CIBLE." >> /tmp/notice
	echo "- Vous sélectionnerez ensuite le(s) dossier(s) ''en vrac'' dont vous" >> /tmp/notice
	echo "  voulez classer les fichiers. C'est (ce sont) le(s) dossier(s) SOURCE." >> /tmp/notice
	echo "- Note : Le dossier CIBLE peut être le même que le dossier SOURCE," >> /tmp/notice
	echo "  si les fichiers sont tous dans un même dossier. Dans ce cas, on ne" >> /tmp/notice
	echo "  peut avoir qu'un seul dossier SOURCE, qui est également la CIBLE..." >> /tmp/notice
	echo "*** Vous pouvez cliquer sur ''Annuler'' pour mettre fin au script ***" >> /tmp/notice
	zenity --text-info --title "Dossier Magique - Mode d'emploi" --height "260" --width "490" --filename "/tmp/notice"
	crd=$?; testcrd
fi

# Option "terminal" pour utilisation non graphique
if [ "$TERMINAL" = "VRAI" ]
then
	DIR="${1}"
# Sinon, exécution en mode graphique
else
	# On sélectionne d'abord le répertoire cible
	DIR=$(zenity --file-selection --title "Dossier Magique - Choisir répertoire CIBLE" --filename "$DIR"/ --directory)
	crd=$?; testcrd
fi


# Option "graphique" ou par défaut pour utilisation graphique
if [ "$TERMINAL" = "FAUX" ]
then
	# Protégeons le séparateur standard et initialisons-le à "|"
	OLDIFS="${IFS}"
	IFS='|'
fi
# Option "terminal" pour utilisation non graphique
if [ "$TERMINAL" = "VRAI" ]
then
	# On recherche le(s) paramètre(s) SOURCE(S) éventuel(s)
	shift
	if [ "${1}" = "" ]
	then
		# Pas de répertoire SOURCE, alors SOURCE = CIBLE
		TABSCE=("$DIR")
	else
		# On charge le (la liste des) dossier(s) SOURCE(S)
		TABSCE=("${@}")
	fi
# Sinon, exécution en mode graphique
else
	# On peut sélectionner un ou plusieurs répertoires à trier
	# (par défaut, on trie dans le même répertoire cible=source)
	TABSCE=($(zenity --file-selection --title "Dossier Magique - Choisir répertoire(s) SOURCE" --filename "$DIR"/ --directory --multiple))
	crd=$?; testcrd
fi

# Définition des répertoires (à adapter si besoin) :
TXT="${DIR}/txt"
PDF="${DIR}/pdf"
AUDIO="${DIR}/musique"
VIDEO="${DIR}/videos"
IMG="${DIR}/images"
ARCHIVES="${DIR}/archives"
DOCS="${DIR}/office"
TEX="${DIR}/documents"
MISC="${DIR}/divers"
BIN="${DIR}/executables"

# Création des répertoires de tri dans le dossier cible
createdirs
for SCE in "${TABSCE[@]}"
do
	# Option "terminal" pour utilisation non graphique
	if [ "$TERMINAL" = "VRAI" ]
	then
		tri "${SCE}"
	else
        tri "${SCE}" | zenity --progress --title "Dossier Magique - Transfert en cours" --auto-close --pulsate --no-cancel
	fi
done

# Restaurons le séparateur et le répertoire courant
IFS="${OLDIFS}"
cd "${OLDDIR}"

# Option "graphique" ou par défaut pour utilisation graphique
if [ "$TERMINAL" = "FAUX" ]
then
	# Restaurons le séparateur standard
	IFS="${OLDIFS}"
	# Nettoyons les fichiers temporaires générés
	rm /tmp/notice /tmp/tri

	zenity --info --title "Dossier Magique" --text "Traitement terminé" --timeout "5"
fi

exit 0
