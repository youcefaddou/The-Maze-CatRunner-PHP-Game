<?php
session_start();
if (isset($_POST['direction']) || isset($_POST['reset'])) {
    $_SESSION['message'] = '';
}
$labyrinthes = [
    [
        "###############",
        "#S............#",
        "#.#####.#####.#",
        "#.#...#.#...#.#",
        "#.#.###.###.#.#",
        "#.#H........#.#",
        "#.###.#####.#.#",
        "#.....#.....#.#",
        "#####.#.#####.#",
        "#.....#......C#",
        "###############"
    ],
    [
        "###############",
        "#S#...#...#.#C#",
        "#.#.#.#.#.#...#",
        "#.#.#.#.#.#.#.#",
        "#...#...#.#.#.#",
        "#.#.#.#H#...#.#",
        "###############"
    ],
    [
        "###############",
        "#.#.#.#.#.#.#.#",
        "#S#.#.#.#.#...#",
        "#.#.#.#.#.#.#H#",
        "#...#...#.#.#.#",
        "#.#...#.....#C#",
        "#.###...###...#",
        "###############"
    ],
    [
        "################",
        "#C............H#",
        "#.#####.#####..#",
        "#.#...#.#...#.##",
        "#.#.###.###.#..#",
        "#.#.....#......#",
        "#.###.#.#####.##",
        "#.....#.....#S##",
        "#####.#####.#..#",
        "#..............#",
        "################"
    ]
];

//initialisat° du labyrinthe
if (!isset($_SESSION['lab']) || isset($_POST['reset'])) {
    $_SESSION['message'] = '';
    $_SESSION['lab'] = $labyrinthes[array_rand($labyrinthes)];  //choix d'un lab random
    $_SESSION['win'] = false;
    $_SESSION['gameOver'] = false;
    $_SESSION['hammer'] = false;
    $_SESSION['life'] = 9;
    $life = $_SESSION['life'];
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

    //deplacement du chat
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
    $messageForThisTurn = '';

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
        } elseif ($case == '#') {
            if (isset($_SESSION['hammer']) && $_SESSION['hammer']) {
                $_SESSION['hammer'] = false;
                $messageForThisTurn = "Vous avez cassé le mur !";
                $_SESSION['lab'][$new_cat_y][$new_cat_x] = '.';
                $catMoved = true;
            } else {
                $messageForThisTurn = "Vous avez heurté le mur, vous perdez une vie !";
                $_SESSION['life']--;
                if ($_SESSION['life'] === 0) {
                    $messageForThisTurn = "Vous avez perdu !";
                    $_SESSION['gameOver'] = true;
                }
            }
        } else {
            $catMoved = true;
        }
    }
    $_SESSION['message'] = $messageForThisTurn; //msg temporaire pour un tour

    //deplacement souris
    if ($catMoved && !$_SESSION['win']) {
        $_SESSION['pos']['x'] = $new_cat_x;
        $_SESSION['pos']['y'] = $new_cat_y;

        $mouse = findMouse($_SESSION['lab']);
        if ($mouse) {
            $directions = [
                ['x' => 0, 'y' => -1], // haut
                ['x' => 0, 'y' => 1],  // bas
                ['x' => -1, 'y' => 0], // gauche
                ['x' => 1, 'y' => 0]   // droite
            ];
            //filtre les directions valides (hors murs et hors nouvelle pos du chat)
            $validDirections = [];
            foreach ($directions as $dir) {
                $new_mouse_x = $mouse['x'] + $dir['x'];
                $new_mouse_y = $mouse['y'] + $dir['y'];

                if (
                    isset($_SESSION['lab'][$new_mouse_y][$new_mouse_x]) &&
                    $_SESSION['lab'][$new_mouse_y][$new_mouse_x] === '.' &&
                    !($new_mouse_x == $new_cat_x && $new_mouse_y == $new_cat_y)
                ) {
                    $validDirections[] = $dir;
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
