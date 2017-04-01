# SOLTools Developer info

SailOnLine races in Google Earth

This program collect boats data on www.sailonline.org and display them on Google Earth.

It is delivered "as this" in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the [GNU General Public License](http://www.gnu.org/licenses/) for more details.

## Folders


### solboats2kml.php : a G.E. view of the boats in race

Theses folders are mandatory

* css: guess!
* gdfonts: data for generating screen overlays for G.E.
* include: all the stuff to keep then display boats in G.E.  
* js: javascript scripts
* kml: where are kept the KML files produced by the soft
* kmz: KML archives in zip (kmz) format
* lang: localisation stuff; add your own traduction here
* sol_include: specific to SOL configuration and connection
* SOLGribXml: where grib files are kept.
NB: These gribs have only 2 or 3 layers of data. For a complete grib file use that ones AG DCChecker produces.
* sources: old stuff for mapping small pictures on G.E. Not used in this version
* sources_3d: boats models (.dae format) and textures 

These folders are optionnal

* images: Where to keep manual screenshots and G.E. screen overlays
* polar: Not used; the software do not produce polar file right now. If so their would be exactly the sames that AG DCChecker does.

### solgribtokml.php : a G.E view of a the current layer of the grib file

Not very interesting. One layer of a grib is produced for G.E.

The data are in the kml/SolGrib folder with the prefix SolWind


### sol_my_boat.php : Display information about your boat in race.  Not very useful yet.

maybe some days this soft will be at the core of a future SolClient :)) 


### Localisation

To add a new language "xx" copy the *./lang/sol2kml_en_utf8.php* to *./lang/sol2kml_xx_utf8.php*

Edit the new file and translate to "xx" language *each second part* of the sentences (after the '='):

> $t_string['key'] = 'This is the key message without any parameter';

> $t_string['key2'] = 'This is the key2 message with the parameter {$a}';

Warning : do not translate the key part !:>))
The {$a} is a token that has to be kept as this and will be set up dynamically in the code.

For exemple
> $t_string['welcome'] = '"Welcome {$a}"';

when coded as

> echo $al->get_string('welcome','John Do');

will output

> "Welcome John Do".

So don't bother with the {$a} parametrers, but keep them in the translation.


That's all folks.

