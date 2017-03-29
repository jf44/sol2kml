# sol2kml
SailOnLine races in Google Earth
This program collect boats data on www.sailonline.org and display them on Google Earth.

It is delivered "as this" in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.

See <http://www.gnu.org/licenses/>.

Documentation
-------------
What's that ?
A web server which gets Sailonline boats positions and export them as a KML / KMZ file for Google Earth

Scripts
----------------
solboats2kml.php : a G.E. view of the boats  in race

solgribtokml.php : a G.E view of a layer of the grib file

sol_my_baot.php : Display information about your boat in race.  Not very useful yet.

How it works
----------------
Boats positions (longitude, latitude) and COG (direction) are sent by the Sol server.
So I compute the speed (SOG) and  TWA from Grib file and Polars, with a few liberties
since I have not implemented exactly the interpolation algorithm that Sol uses.

If anybody can gives me hints about how Sol compute interpolation, I'll implemented it...

The sails (jib, genois, gennaker or spinnaker) are deduced from TWA (if greater than 140° spinnaker is displayed).

The scale determines the size of the boats on the map.
Set scale to 0.1 (or less) for narrows, 0.5 for coastal races, 2 to 15 for transocean races.

Improvements of that version :
- localisation (fr, en).
- getting Grib, Polar, Marks, Tracks
- setting TWD, TWS, TWA, SOG from grib and polars
- computation of boat roll and tilt from TWS / TWA and the right sail (jib, genois, gennaker, spi)
- tour of the fleet
- monohull or multihull (trimaran)

Next improvements
- choose boats to display
- new boats like catamarans, iceboats, etc.

Localisation
------------
To add a new language "xx" copy the ./lang/sol2kml_en_utf8.php to ./lang/sol2kml_xx_utf8.php;

Edit the new file and translate to "xx" each second part of the sentences:
- like $t_string['key'] = 'This is a message withhout any value parameter to display';
- like $t_string['key2'] = 'This is a message with the value {$a} to display';

Warning : do not translate the key part !:>))

Improvements
------------
If you like to get others improvements send me a mail.

JF44 : jean.fruitet@free.fr

Needs
-----
A) You need Google Earth to display the KML/KMZ files
B) You need a web server which allows the PHP fonction file_get_contents($url) to collect the data
directly from the SailOnLine race server and generate the KML / KMZ files.

So you have to set up a local web server to test and produce your own G.E maps.
Look at Apachefriends' Xampp for exemple.

But in the case of you own a personnal Web server on the Internet whith the PHP fonction file_get_contents($SolServerUrl)
activated, the visitors of your site may generate their own maps online.

Installation
------------
Unzip the sol2kml.zip archive in a folder of your local webserver, for exemple
./hdocs/soltools/

Usage
-----
Connect with a Web browser to the URL
<a href="http://localhost/soltools/solboats2kml.php?lang=en">http://localhost/soltools/solboats2kml.php?lang=en</a>

Choose a race
Select a scale
Select a boat model
Click the yellow "Validate" button...
Wait a while... Two KMZ and one KML files are produced.
Open one of them with G.E.


That's all folks.

