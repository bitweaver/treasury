#!/bin/bash
echo Bitweaver TreasuryPackage package creator
echo
# Validate Input
if [ $# == 0 ]
then
	echo "Usage: mkpackage packagename, gallery, galleries, item, items"
	exit
fi

package=$1
gallery=$2
galleries=$3
item=$4
items=$5

# check a package was specified
if [ "$package" == "" ]
then
	echo "Please enter a package name to create"
	exit
fi

# Make the correct case copies of the params
lcase=`echo "$package" | perl -ne "print lc"`
ucase=`echo "$package" | perl -ne "print uc"`
ccase=`echo "$lcase" | perl -n -e "print ucfirst"`

gclcase=`echo "bit$gallery" | perl -ne "print lc"`
gcucase=`echo "BIT$gallery" | perl -ne "print uc"`
galleryuc=`echo "$gallery" | perl -n -e "print ucfirst"`
gcccase="Bit$galleryuc"

glcase=`echo "$gallery" | perl -ne "print lc"`
gucase=`echo "$gallery" | perl -ne "print uc"`
gccase=`echo "$glcase" | perl -n -e "print ucfirst"`

gslcase=`echo "$galleries" | perl -ne "print lc"`
gsucase=`echo "$galleries" | perl -ne "print uc"`
gsccase=`echo "$gslcase" | perl -n -e "print ucfirst"`

iclcase=`echo "bit$item" | perl -ne "print lc"`
icucase=`echo "BIT$item" | perl -ne "print uc"`
itemuc=`echo "$item" | perl -n -e "print ucfirst"`
icccase="Bit$itemuc"

ilcase=`echo "$item" | perl -ne "print lc"`
iucase=`echo "$item" | perl -ne "print uc"`
iccase=`echo "$ilcase" | perl -n -e "print ucfirst"`

islcase=`echo "$items" | perl -ne "print lc"`
isucase=`echo "$items" | perl -ne "print uc"`
isccase=`echo "$islcase" | perl -n -e "print ucfirst"`

# Check that the package doesn't already exist
if [ -d $lcase ]
then
	echo "A package called $ccase already exists. Folder $lcase exists"
	exit
fi

#is the package called the module name
if [[ ( -d _bit_treasury ) && ( ! -d treasury ) ]]
then
	#call the package treasury instead
	mv _bit_treasury treasury
fi

# if we have the treasury package
if [ -d treasury ]
then
	# From http://www.bitweaver.org/wiki/TreasuryPackage
	echo Rename Treasury
	mv treasury $lcase; cd $lcase
	echo Case sensitive Search and Replace all occureneces of 'treasurygallery' with $gclcase
	find . -name "*" -type f -exec perl -i -wpe "s/treasurygallery/$gclcase/g" {} \;
	find . -name "*" -type f -exec perl -i -wpe "s/TREASURYGALLERY/$gcucase/g" {} \;
	find . -name "*" -type f -exec perl -i -wpe "s/TreasuryGallery/$gcccase/g" {} \;
	echo Case sensitive Search and Replace all occureneces of 'galleries' with $gslcase
	find . -name "*" -type f -exec perl -i -wpe "s/galleries/$gslcase/g" {} \;
	find . -name "*" -type f -exec perl -i -wpe "s/Galleries/$gsccase/g" {} \;
	echo Case sensitive Search and Replace all occureneces of 'gallery' with $glcase
	find . -name "*" -type f -exec perl -i -wpe "s/gallery/$glcase/g" {} \;
	find . -name "*" -type f -exec perl -i -wpe "s/Gallery/$gccase/g" {} \;

	echo Case sensitive Search and Replace all occureneces of 'treasuryitem' with $islcase
	find . -name "*" -type f -exec perl -i -wpe "s/treasuryitem/$iclcase/g" {} \;
	find . -name "*" -type f -exec perl -i -wpe "s/TREASURYITEM/$icucase/g" {} \;
	find . -name "*" -type f -exec perl -i -wpe "s/TreasuryItem/$icccase/g" {} \;
	# we must do plural first
	echo Case sensitive Search and Replace all occureneces of 'items' with your $islcase
	find . -name "*" -type f -exec perl -i -wpe "s/items/$islcase/g" {} \;
	find . -name "*" -type f -exec perl -i -wpe "s/Items/$isccase/g" {} \;
	echo Case sensitive Search and Replace all occureneces of 'item' with your $ilcase
	find . -name "*" -type f -exec perl -i -wpe "s/item/$ilcase/g" {} \;
	find . -name "*" -type f -exec perl -i -wpe "s/Item/$iccase/g" {} \;

	echo Case sensitive Search and Replace all occureneces of 'treasury' with $lcase
	find . -name "*" -type f -exec perl -i -wpe "s/treasury/$lcase/g" {} \;
	find . -name "*" -type f -exec perl -i -wpe "s/TREASURY/$ucase/g" {} \;
	find . -name "*" -type f -exec perl -i -wpe "s/Treasury/$ccase/g" {} \;

	echo Rename all the files containing 'treasurygallery' with $gclcase
	find . -name "*treasurygallery*" -exec rename treasurygallery $gclcase {} \;
	find . -name "*TreasuryGallery*" -exec rename TreasuryGallery $gcccase {} \;
	echo Rename all the files containing 'treasuryitem' with $iclcase
	find . -name "*treasuryitem*" -exec rename treasuryitem $iclcase {} \;
	find . -name "*TreasuryItem*" -exec rename TreasuryItem $icccase {} \;
	echo Rename all the files containing 'galleries' with $gslcase 
	find . -name "*galleries*" -exec rename galleries $gslcase {} \;
	find . -name "*Galleries*" -exec rename Galleries $gsccase {} \;
	echo Rename all the files containing 'items' with $islcase 
	find . -name "*items*" -exec rename items $islcase {} \;
	find . -name "*Items*" -exec rename Items $isccase {} \;
	echo Rename all the files containing 'gallery' with $glcase 
	find . -name "*gallery*" -exec rename gallery $glcase {} \;
	find . -name "*Gallery*" -exec rename Gallery $gccase {} \;
	echo Rename all the files containing 'item' with $ilcase 
	find . -name "*item*" -exec rename item $ilcase {} \;
	find . -name "*Item*" -exec rename Item $iccase {} \;
	echo Rename all the files containing 'treasury' with $lcase 
	find . -name "*treasury*" -exec rename treasury $lcase {} \;
	find . -name "*Treasury*" -exec rename Treasury $ccase {} \;
	cd ..

	echo
	echo A basic outline of your package $ccase has been created
	echo
else
	echo directory treasurys not found
	echo please review any errors
	echo please download and decompress the treasurys package
fi

