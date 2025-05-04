//------------------------------------------
// Polygonal dice
//------------------------------------------

//------------------------------------------
// Geometric regular solids in OpenSCAD
//------------------------------------------

//------------------------------------------
// https://www.thingiverse.com/thing:1043661 Original
// https://www.thingiverse.com/thing:3472349 Modified


size = 15;
tetraheight = size*1.35; // 4 Sided
cubeheight  = size; // 6 Sided
octaheight  = size; // 8 Sided
deltoheight = size; // 10 Sided
dodeheight  = size; // 12 Sided
icoheight   = size; // 20 Sided

name = "tetraedro"; //[tetrahedron, cube, octahedron, decagon, dodecahedron, icosahedron]

dice_to_draw = dice(name);
cut_corners = true; // [true, false]

$fontSize = 1; //[0:0.1:2]
$font ="Impact:style=Regular";//["Centaur:style=Regular","Century:style=Regular","Chiller:style=Regular", "Comic Sans MS:style=Regular""Consolas:style=Regular", "Cookie:style=Regular","Corbel:style=Bold","Curlz MT:style=Regular", "David CLM:style=Medium","DejaVu Sans:style=Condensed Oblique","Euphorigenic:style=Regular","Freestyle Script:style=Regular","High Tower Text:style=Regular","Impact:style=Regular","Ink Free:style=Regular", "Jokerman:style=Regular", "Linux Libertine G:style=Regular", "Impact:style=Regular"]


module dodecahedron(height,slope,cutoff) {
    intersection() {
        // Make a cube
        cube([2 * height, 2 * height, cutoff * height], center = true); 

        // Loop i from 0 to 4, and intersect results
        intersection_for(i = [0:4]) { 
            // Make a cube, rotate it 116.565 degrees around the X axis,
            // then 72 * i around the Z axis
            rotate([0, 0, 72 * i])
            rotate([slope, 0, 0])
            cube([2 * height, 2 * height, height], center = true); 
        }
    }
}

module deltohedron(height) {
    slope = 132;
    cut_modifier = 1.43;
    
    rotate([48, 0, 0])
    intersection() {
        dodecahedron(height,slope,2);
        
        if(cut_corners)
        cylinder(
            d=cut_modifier*height*0.99,
            h=1.3*height,
            center = true);
    }
}

module octahedron(height) {
    intersection() {
        // Make a cube
        cube([2 * height, 2 * height, height], center = true); 

        // Loop i from 0 to 2, and intersect results
        intersection_for(i = [0:2]) { 
            // Make a cube, rotate it 109.47122 degrees around the X axis,
            // then 120 * i around the Z axis
            rotate([109.47122, 0, 120 * i])
            cube([2 * height, 2 * height, height], center = true); 
        }
    }
}


    w=-15.525;

module icosahedron(height) {
    intersection() {
        octahedron(height);

        rotate([0, 0, 60 + w])
            octahedron(height);

        intersection_for(i = [1:3]) { 
            rotate([0, 0, i * 120])
            rotate([109.471, 0, 0])
            rotate([0, 0, w])
            octahedron(height);
        }
    }
}

module tetrahedron(height) {
    scale([height, height, height]) {	// Scale by height parameter
        polyhedron(
            points = [
                [-0.288675, 0.5, /* -0.27217 */ -0.20417],
                [-0.288675, -0.5, /* -0.27217 */ -0.20417],
                [0.57735, 0, /* -0.27217 */ -0.20417],
                [0, 0, /* 0.54432548 */ 0.612325]
            ],
            faces = [
                [1, 2, 0],
                [3, 2, 1],
                [3, 1, 0],
                [2, 3, 0]
            ]
        );
    }
}

//------------------------------------------
// Text modules
//------------------------------------------

textvals=["1", "2", "3", "4", "5", "6", "7", "8",
	"9", "10", "11", "12", "13", "14", "15",
	"16", "17", "18", "19", "20"];

underscore=[" ", " ", " ", " ", "_", " ", " ", " ",
	" ", " ", " ", " ", " ", " ",
	"_", " ", " ", " ", " ", " "];

dounderscore=[" ", " ", " ", "_", "_", " ", " ", " ", " ", " ", " ", " "];
dotext=     ["2", "11", "4", "9", "6", "7", "5", "8", "3", "10"];
dunderscore=[" ", "_", " ", " ", " ", " ", "_", " ", " ", " ", " ", " "];
dtext=["0", "9", "8", "1", "2", "7", "6", "3", "4", "5"];
nunderscore=[" ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " "];
ddtext=["00", "90", "80", "10", "20", "70", "60", "30", "40", "50"];

ttext=["1", "2", "3", "4", "3", "2", "4", "2", "1", "4", "1", "3"];

//otext=["1", "2", "3", "15", "19", "6", "7", "8",
//	"9", "10", "12", "4", "20", "5", "14",
//	"11", "18", "17", "13", "16"];
// Order by TheBaron on Thingiverse
otext = ["17","15","3","10","6","11","12","2","5","18","16","8","4","1","9","3","14","13","19","20"];

module tetratext(height) {
    text_multiplier = 0.24 * $fontSize;
    text_depth = 0.8;
    text_push = 0.26;
    rotate([180, 0, 0])
    translate([0, 0, 0.2 * height - text_depth])
    for (i = [0:2]) { 
        rotate([0, 0, 120 * i])
        translate([text_push * height, 0, 0])
        rotate([0, 0, -90])
        linear_extrude(height=2)
        text(ttext[i], size=text_multiplier * height,
			valign="center", halign="center", font=$font);
    }

    for (j = [0:2]) { 
        rotate([0, -70.5288, j * 120])
        translate([0, 0, 0.2 * height - text_depth])
        for (i = [0:2]) {
            rotate([0, 0, 120 * i])
            translate([text_push * height, 0, 0])
            rotate([0, 0, -90])
            linear_extrude(height=2.5)
            text(ttext[(j + 1) * 3 + i], size=text_multiplier * height,
                valign="center", halign="center", font=$font);
        }
    }
}

module octatext(height) {
    text_multiplier = 0.5 * $fontSize;
    text_depth = 0.8;

    rotate([0, 0, 180])
    translate([0, 0, 0.5 * height - text_depth])
    linear_extrude(height=2)
	text("1", size=text_multiplier * height,
			valign="center", halign="center", font=$font);

    translate([0, 0, -0.5 * height + text_depth])
    rotate([0, 180, 180])
    linear_extrude(height=2)
	text("8", size=text_multiplier * height,
			valign="center", halign="center", font=$font);

    // Loop i from 0 to 2, and intersect results
    for (i = [0:2]) { 
        rotate([109.47122, 0, 120 * i]) {
            translate([0, 0, 0.5 * height - text_depth])
            linear_extrude(height=2.5)
            text(textvals[i*2 + 1], size=text_multiplier * height,
                valign="center", halign="center", font=$font);

            translate([0, 0, -0.5 * height + text_depth])
            rotate([0, 180, 180])
            linear_extrude(height=2.5)
            text(textvals[6 - i*2], size=text_multiplier * height,
                valign="center", halign="center", font=$font);
        }
    }
}

module octahalf(height, j) {
    rotate([0, 0, 180]) {
        rotate([0, 0, 39])
        translate([0, 0, 0.5 * height - 1])
        linear_extrude(height=2)
        text(otext[j], size=0.21 * height * $fontSize,
            valign="center", halign="center", font=$font);

        rotate([0, 0, 39])
        translate([0, 4, 0.5 * height - 1])
        linear_extrude(height=2)
        text(underscore[j], size=0.21 * height,
            valign="center", halign="center", font=$font);
    }

    // Loop i from 0 to 2, and intersect results
    for (i = [0:2]) { 
        rotate([109.47122, 0, 120 * i]) {
            rotate([0, 0, 39])
            translate([0, 0, 0.5 * height - 1])
            linear_extrude(height=2.5)
            text(otext[i + j + 1], size=0.21 * height * $fontSize,
                valign="center", halign="center", font=$font);

            rotate([0, 0, 39])
            translate([0, 4, 0.5 * height - 1])
            linear_extrude(height=2.5)
            text(underscore[i + j + 1], size=0.21 * height * $fontSize,
                valign="center", halign="center", font=$font);
        }
    }
}

module cubetext(height) {

    rotate([0, 0, 180])
    translate([0, 0, 0.5 * height - 1])
	linear_extrude(height=2)
	text("1", size=height * 0.6 * $fontSize, valign="center", halign="center", font=$font);

    translate([0, 0, -0.5 * height + 1])
    rotate([0, 180, 180])
    linear_extrude(height=2)
	text("6", size=height * 0.6 * $fontSize, valign="center", halign="center", font=$font);

    // Loop i from 0 to 2, and intersect results
    for (i = [0:1]) { 
        rotate([90, 0, 90 * i]) {
            translate([0, 0, 0.5 * height - 1])
            linear_extrude(height=2.5)
            text(textvals[i*2 + 1], size=0.6 * height * $fontSize,
                valign="center", halign="center", font=$font);

            translate([0, 0, -0.5 * height + 1])
            rotate([0, 180, 180])
            linear_extrude(height=2.5)
            text(textvals[4 - i*2], size=height * 0.6 * $fontSize,
                valign="center", halign="center", font=$font);
        }
    }
}

module dodecatext(height,slope) {
    text_multiplier = 0.32;
    text_depth = 0.8;
    
    rotate([0, 0, 180])
    translate([0, 0, 0.5 * height - text_depth])
    linear_extrude(height=2)
	text("12", size=text_multiplier * height * $fontSize, valign="center", halign="center", font=$font);

    translate([0, 0, -0.5 * height + text_depth])
    rotate([0, 180, 0])
    linear_extrude(height=1)
	text("1", size=text_multiplier * height * $fontSize, valign="center", halign="center", font=$font);
    
    deltotext(height,slope,0.6,0,0,dotext,dounderscore);
}

module deltotext(
    height,
    angle,
    text_depth,
    text_push,
    text_offset,
    text_array,
    underscore_array) {
        
    text_multiplier = 0.32 * $fontSize;
    
    
    
    // Loop i from 0 to 4, and intersect results
    for (i = [0:4]) { 
        rotate([0, 0, 72 * i])
        rotate([angle, 0, 0]) {
            translate([0, text_push, 0.5 * height - text_depth])
            linear_extrude(height=2.5)
            text(text_array[i*2 + text_offset], size=text_multiplier * height,
                valign="center", halign="center", font=$font);

            translate([0, text_push+(-height*0.18), 0.5 * height - text_depth])
            linear_extrude(height=2.5)
            text(underscore_array[i*2 + text_offset], size=text_multiplier * height,
                valign="center", halign="center", font=$font);

            translate([0, -text_push, -0.5 * height + text_depth])
            rotate([0, 180, 180])
            linear_extrude(height=2.5)
            text(text_array[i*2 + text_offset+1], size=text_multiplier * height,
                valign="center", halign="center", font=$font);

            translate([0, -text_push+(height*0.23), -0.5 * height + text_depth])
            rotate([0, 180, 0])
            linear_extrude(height=2.5)
            text(underscore_array[i*2 + text_offset+1], size=text_multiplier * height,
                valign="center", halign="center", font=$font);
        }
    }
}

module icosatext(height) {

    rotate([70.5288, 0, 60])
    octahalf(height, 0);

    rotate([0, 0, 60 + w]) {
        octahalf(height, 4);
    }

    for(i = [1:3]) { 
        rotate([0, 0, i * 120])
        rotate([109.471, 0, 0])
        rotate([0, 0, w])
	    octahalf(height, 4 + i * 4);
    }
}

//------------------------------------------
// Complete dice
//------------------------------------------


module drawtetra() {
    translate ([0, 0, tetraheight * 0.2])
    difference() {
        intersection() {
            tetrahedron(tetraheight);
            if(cut_corners)
            rotate([0, 180, 0])
                tetrahedron(tetraheight*3*0.9);
        }
        tetratext(tetraheight);
    }
}

module drawcube() {
    translate([0, 0, cubeheight*0.5]) {
        difference() {
            intersection() {
                cube([cubeheight, cubeheight, cubeheight], center = true);
                if(cut_corners)
                rotate([125, 0, 45])
                octahedron(cubeheight*1.6);
            }
            cubetext(cubeheight);
        }
    }
}

module drawocta() {
    translate ([0, 0, octaheight*0.5]) {
        difference() {
            intersection() {
                octahedron(octaheight);
                if(cut_corners)
                rotate([45, 35, -30])
                    cube([octaheight*1.61111,
                        octaheight*1.61111,
                        octaheight*1.61111],
                        center = true);
            }
            octatext(octaheight);
        }
    }
}

module drawdelto() {
    translate ([0, 0, deltoheight*0.5]) {
        difference() {
            deltohedron(deltoheight);
//            if(cut_corners)
            rotate([48, 0, ])
            deltotext(deltoheight,132,1,3,0,dtext,dunderscore);
        }
    }
}

module drawdeltodec() {
    translate ([0, 0, deltoheight*0.5]) {
        difference() {
            deltohedron(deltoheight);
//            if(cut_corners)
            rotate([48, 0, ])
            deltotext(deltoheight,132,1,3,0,ddtext,nunderscore);
        }
    }
}

module drawdodeca() {
    translate ([0, 0, dodeheight*0.5]) {
        difference() {
            intersection() {
                dodecahedron(dodeheight,116.565,1);
                
                if(cut_corners)
                rotate([35, 10, -18])
                    icosahedron(dodeheight*1.218);
            }
            dodecatext(dodeheight,116.565);
        }
    }
}

module drawicosa() {
    translate ([0, 0, icoheight*0.5]) {
        difference() {
            intersection() {
                icosahedron(icoheight);
                
                if(cut_corners)
                rotate([-10, 35, -28])
                    dodecahedron(icoheight*1.2,116.565,1);
            }
            icosatext(icoheight);
        }
    }
}


positions = [
    [[0,0,0]],
    [[-15,0,0],[15,0,0]],
    [[-30,0,0],[0,0,0],[30,0,0]],
    [[-15,-15,0],[15,-15,0],[-15,15,0],[15,15,0]],
    [[-30,30,0],[-30,-30,0],[0,0,0],[30,30,0],[30,-30,0]],
    [[-30,30,0],[-30,-30,0],[0,0,0],[30,30,0],[30,-30,0],[60,-30,0]],
    [[-30,30,0],[-30,-30,0],[0,0,0],[30,30,0],[30,-30,0],[60,-30,0],[60,30,0]]
];

module drawWhich(which="6") {
    if(which=="4") drawtetra();
    if(which=="6") drawcube();
    if(which=="8") drawocta();
    if(which=="10") drawdelto();
    if(which=="00") drawdeltodec();
    if(which=="12") drawdodeca();
    if(which=="20") drawicosa();
}

module drawPolyset(polyset=["4","6","8","10","00","12","20"]) {
    polylength = len(polyset)-1;
    for(i = [0:polylength]) {
        translate(positions[polylength][i])
            drawWhich(polyset[i]);
    }
}

drawPolyset(dice_to_draw);

function dice(name) = 
name == "tetrahedron" ? ["4"] :
name == "cube"? ["6"] :
name == "octahedron" ? ["8"] :
name == "decagon" ? ["10"] :
name == "dodecahedron" ? ["12"] :
name == "icosahedron" ? ["20"] :
["4", "6", "8", "10", "00", "12", "20"];
