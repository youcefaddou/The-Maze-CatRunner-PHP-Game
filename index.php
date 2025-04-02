<?php require 'maze.php'; ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Maze Cat Runner</title>
    <link rel="stylesheet" href="./style.css">
</head>

<body>
    <h1>The Maze Cat Runner</h1>

    <div id="mazeContainer">
        <?php if (isset($_SESSION['win']) && $_SESSION['win']) : ?>
            <div class="win-message">
                <p>Gagné ! Le chat a attrapé la souris !</p>
                <form method="post">
                    <button type="submit" name="reset">Rejouer</button>
                </form>
            </div>
        <?php else: ?>
            <div class="game-area">
                <table class="maze">
                    <?php
                    if (isset($_SESSION['lab'])) {
                        foreach ($_SESSION['lab'] as $y => $ligne) {
                            echo '<tr>';
                            for ($x = 0; $x < strlen($ligne); $x++) {
                                $diffY = $y - $_SESSION['pos']['y'];
                                $diffX = $x - $_SESSION['pos']['x'];
                                $visible = ($diffY >= -1 && $diffY <= 1) && ($diffX >= -1 && $diffX <= 1);

                                echo '<td>';
                                if ($visible) {
                                    if ($x == $_SESSION['pos']['x'] && $y == $_SESSION['pos']['y']) {
                                        echo '<div class="cat-container">';
                                        echo '<img src="./images/chat.png" alt="chat" class="cat-img">';
                                        if (isset($_SESSION['hammer']) && $_SESSION['hammer']) {
                                            echo '<img src="./images/marteau.png" alt="marteau" class="hammer-equipped">';
                                        }
                                        echo '</div>';
                                    } else {
                                        $case = $_SESSION['lab'][$y][$x]; //utilise le labyrinthe modifiable
                                        if ($case == '#') {
                                            echo '<img src="./images/mur.png" alt="mur">';
                                        } elseif ($case == 'H') {
                                            echo '<img src="./images/marteau.png" alt="marteau">';
                                        } elseif ($case == 'S') {
                                            echo '<img src="./images/souris.png" alt="souris">';
                                        }  else {
                                            echo '&nbsp;';
                                        }
                                    }
                                } else {
                                    echo '<img src="./images/brouillard.png" alt="Brouillard">';
                                }
                                echo '</td>';
                            }
                            echo '</tr>';
                        }
                    }
                    ?>
                </table>
                <form method="post" class="replay">
                    <button type="submit" name="reset">Rejouer</button>
                </form>
                <div class="controls">
                    <form method="post">
                        <button type="submit" name="direction" value="up"><img src="./images/la-haut.png" alt="Haut"></button>
                        <button type="submit" name="direction" value="left"><img src="./images/la-gauche.png" alt="Gauche"></button>
                        <button type="submit" name="direction" value="right"><img src="./images/la-droite.png" alt="Droite"></button>
                        <button type="submit" name="direction" value="down"><img src="./images/la-bas.png" alt="Bas"></button>
                    </form>
                </div>
            </div>
            <?php if (!empty($_SESSION['message'])): ?>
                <p class="error-message"><?php echo $_SESSION['message']; ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>

</html>