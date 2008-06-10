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
galleriess=$3
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

gclcase=`echo "bit$treasury" | perl -ne "print lc"`
gcucase=`echo "BIT$treasury" | perl -ne "print uc"`
gcccase=`echo "Bit$glcase" | perl -n -e "print ucfirst"`

glcase=`echo "$treasury" | perl -ne "print lc"`
gucase=`echo "$treasury" | perl -ne "print uc"`
gccase=`echo "$glcase" | perl -n -e "print ucfirst"`

gslcase=`echo "$treasurys" | perl -ne "print lc"`
gsucase=`echo "$treasurys" | perl -ne "print uc"`
gsccase=`echo "$gslcase" | perl -n -e "print ucfirst"`

iclcase=`echo "bit$item" | perl -ne "print lc"`
icucase=`echo "BIT$item" | perl -ne "print uc"`
icccase=`echo "Bit$ilcase" | perl -n -e "print ucfirst"`

ilcase=`echo "$item" | perl -ne "print lc"`
iucase=`echo "$item" | perl -ne "print uc"`
iccase=`echo "$ilcase" | perl -n -e "print ucfirst"`

islcase=`echo "$item" | perl -ne "print lc"`
isucase=`echo "$item" | perl -ne "print uc"`
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
	echo Case sensitive Search and Replace all occureneces of 'treasurygallery' with your treasurygallery class name
	find . -name "*" -type f -exec perl -i -wpe "s/treasurygallery/$gclcase/g" {} \;
	find . -name "*" -type f -exec perl -i -wpe "s/TREASURYGALLERY/$gcucase/g" {} \;
	find . -name "*" -type f -exec perl -i -wpe "s/TreasuryGallery/$gcccase/g" {} \;
	echo Case sensitive Search and Replace all occureneces of 'galleries' with your galleries name
	find . -name "*" -type f -exec perl -i -wpe "s/galleries/$gslcase/g" {} \;
	find . -name "*" -type f -exec perl -i -wpe "s/Galleries/$gsccase/g" {} \;
	echo Case sensitive Search and Replace all occureneces of 'gallery' with your gallery name
	find . -name "*" -type f -exec perl -i -wpe "s/gallery/$glcase/g" {} \;
	find . -name "*" -type f -exec perl -i -wpe "s/Gallery/$gccase/g" {} \;

	echo Case sensitive Search and Replace all occureneces of 'treasuryitem' with your item class name
	find . -name "*" -type f -exec perl -i -wpe "s/treasuryitem/$iclcase/g" {} \;
	find . -name "*" -type f -exec perl -i -wpe "s/TREASURYITEM/$icucase/g" {} \;
	find . -name "*" -type f -exec perl -i -wpe "s/TreasryItem/$icccase/g" {} \;
	# we must do plural first
	echo Case sensitive Search and Replace all occureneces of 'items' with your item name
	find . -name "*" -type f -exec perl -i -wpe "s/items/$islcase/g" {} \;
	find . -name "*" -type f -exec perl -i -wpe "s/Items/$isccase/g" {} \;
	echo Case sensitive Search and Replace all occureneces of 'item' with your item name
	find . -name "*" -type f -exec perl -i -wpe "s/item/$ilcase/g" {} \;
	find . -name "*" -type f -exec perl -i -wpe "s/Item/$iccase/g" {} \;

	echo Case sensitive Search and Replace all occureneces of 'treasurys' with your package name
	find . -name "*" -type f -exec perl -i -wpe "s/treasurys/$lcase/g" {} \;
	find . -name "*" -type f -exec perl -i -wpe "s/TREASURYS/$ucase/g" {} \;
	find . -name "*" -type f -exec perl -i -wpe "s/Treasury/$ccase/g" {} \;

	echo Rename all the files containing 'treasurygallery' with your class name
	find . -name "*treasurygallery*" -exec rename sample $gclcase {} \;
	find . -name "*TreasuryGallery*" -exec rename Sample $gcccase {} \;
	echo Rename all the files containing 'treasuryitem' with your class name
	find . -name "*treasuryitem*" -exec rename sample $iclcase {} \;
	find . -name "*TreasuryItem*" -exec rename Sample $icccase {} \;
	echo Rename all the files containing 'galleries' with your class name
	find . -name "*galleries*" -exec rename sample $gslcase {} \;
	find . -name "*Galleries*" -exec rename Sample $gsccase {} \;
	echo Rename all the files containing 'items' with your class name
	find . -name "*items*" -exec rename sample $islcase {} \;
	find . -name "*Items*" -exec rename Sample $isccase {} \;
	echo Rename all the files containing 'gallery' with your class name
	find . -name "*gallery*" -exec rename sample $glcase {} \;
	find . -name "*Gallery*" -exec rename Sample $gccase {} \;
	echo Rename all the files containing 'item' with your class name
	find . -name "*item*" -exec rename sample $ilcase {} \;
	find . -name "*Item*" -exec rename Sample $iccase {} \;
	echo Rename all the files containing 'treasury' with your package name
	find . -name "*treasury*" -exec rename sample $lcase {} \;
	find . -name "*Treasury*" -exec rename Sample $ccase {} \;
	cd ..

	echo
	echo A basic outline of your package $ccase has been created
	echo
else
	echo directory treasurys not found
	echo please review any errors
	echo please download and decompress the treasurys package
fi

