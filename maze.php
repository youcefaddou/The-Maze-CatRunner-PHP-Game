<?php
session_start();
if (isset($_POST['direction']) || isset($_POST['reset'])) {
    $_SESSION['message'] = '';
}

$labyrinthes = [
    [
        "################",
        "#S............##",
        "#.#####.#####.##",
        "#.#...#.#...#.##",
        "#.#.###.###.#.##",
        "#.#H........#.##",
        "#.###.#####.#.##",
        "#.....#...G.#.##",
        "#####.#.#####.##",
        "#.....#......C##",
        "################"
    ],
    [
        "###############",
        "#S#...#...#.#C#",
        "#.#.#.#.#.#...#",
        "#.#.#.#.#.#.#.#",
        "#...#...#G#.#.#",
        "#.#.#.#H#...#.#",
        "###############"
    ],
    [
        "################",
        "#C............H#",
        "#.#####.#####.##",
        "#.#...#.#...#.##",
        "#G#.###.###.#.##",
        "#.#.....#.....##",
        "#.###.#.#####.##",
        "#.....#.....#S##",
        "#####.#####.#.##",
        "#..............#",
        "################"
    ]
];
//initialisat° du labyrinthe
if (!isset($_SESSION['lab']) || isset($_POST['reset'])) {
    $_SESSION['message'] = '';
    $_SESSION['lab'] = $labyrinthes[array_rand($labyrinthes)];  //choix d'un labyrinthe aléatoire
    $_SESSION['win'] = false;
    $_SESSION['hammer'] = false;
    $_SESSION['grass'] = false;
    //trouver la position du chat
    foreach ($_SESSION['lab'] as $y => $ligne) {
        $x = strpos($ligne, 'C');
        if ($x !== false) {
            $_SESSION['pos'] = ['x' => $x, 'y' => $y];
            break;
        }
    }
}
//trouver la souris
function findMouse($lab)
{
    foreach ($lab as $y => $ligne) {
        $x = strpos($ligne, 'S');
        if ($x !== false) {
            return ['x' => $x, 'y' => $y];
        }
    }
    return null;
}
if (isset($_POST['direction'])) {
    $_SESSION['message'] = '';
    //déplacer le chat en 1er
    $new_cat_x = $_SESSION['pos']['x'];
    $new_cat_y = $_SESSION['pos']['y'];

    switch ($_POST['direction']) {
        case 'up':
            $new_cat_y--;
            break;
        case 'down':
            $new_cat_y++;
            break;
        case 'left':
            $new_cat_x--;
            break;
        case 'right':
            $new_cat_x++;
            break;
    }
    //verification déplacement chat
    $catMoved = false;
    $messageForThisTurn = ''; //variable temporaire pr le message
    
    if (isset($_SESSION['lab'][$new_cat_y][$new_cat_x])) {
        $case = $_SESSION['lab'][$new_cat_y][$new_cat_x];
        if ($case == 'S') {
            $_SESSION['win'] = true;
            $catMoved = true;
        } elseif ($case == 'H') {
            $_SESSION['hammer'] = true;
            $messageForThisTurn = "Vous avez trouvé un marteau, vous pouvez casser un mur.";
            $_SESSION['lab'][$new_cat_y][$new_cat_x] = '.';
            $catMoved = true;
        } elseif ($case === 'G') {
            $_SESSION['grass'] = true;
            
        }
         elseif ($case == '#') {
            if (isset($_SESSION['hammer']) && $_SESSION['hammer']) {
                $_SESSION['hammer'] = false;
                $messageForThisTurn = "Vous avez cassé le mur !";
                $_SESSION['lab'][$new_cat_y][$new_cat_x] = '.';
                $catMoved = true;
            } else {
                $messageForThisTurn = "Attention il y a un mur, déplacement impossible !";
            }
        } else {
            $catMoved = true;
        }
    }
    //stocker le message uniquement pour ce tour
    $_SESSION['message'] = $messageForThisTurn;

    //ensuite déplacer souris seulement si le chat a bougé et pas gagné
    if ($catMoved && !$_SESSION['win']) {
        //sauvegarde ancienne position chat
        $old_cat_x = $_SESSION['pos']['x'];
        $old_cat_y = $_SESSION['pos']['y'];
        //MAJ position chat
        $_SESSION['pos']['x'] = $new_cat_x;
        $_SESSION['pos']['y'] = $new_cat_y;
        //trouve la souris
        $mouse = findMouse($_SESSION['lab']);
        if ($mouse) {
            $directions = [
                ['x' => 0, 'y' => -1], // haut
                ['x' => 0, 'y' => 1],  // bas
                ['x' => -1, 'y' => 0], // gauche
                ['x' => 1, 'y' => 0]   // droite
            ];
            //filtre les directions valides (hors murs et hors nouvelle position du chat)
            $validDirections = [];
            foreach ($directions as $dir) {
                $new_mouse_x = $mouse['x'] + $dir['x'];
                $new_mouse_y = $mouse['y'] + $dir['y'];
                
                if (isset($_SESSION['lab'][$new_mouse_y][$new_mouse_x])) {
                    $cell = $_SESSION['lab'][$new_mouse_y][$new_mouse_x];
                    if ($cell === '.' && !($new_mouse_x == $new_cat_x && $new_mouse_y == $new_cat_y)) {
                        $validDirections[] = $dir;
                    }
                }
            } 
            //deplacement souris si possible
            if (!empty($validDirections)) {
                $chosenDir = $validDirections[array_rand($validDirections)];
                $new_mouse_x = $mouse['x'] + $chosenDir['x'];
                $new_mouse_y = $mouse['y'] + $chosenDir['y'];
                
                $_SESSION['lab'][$mouse['y']][$mouse['x']] = '.';
                $_SESSION['lab'][$new_mouse_y][$new_mouse_x] = 'S';
            }
        }
    }
}
//si le bouton "rejouer" est clické, on detruit la session
if (isset($_POST['reset'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
