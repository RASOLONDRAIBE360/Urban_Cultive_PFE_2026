<h1> Bonjour à vous, <span style="font-weight: bold; font-size: 28px; color:rgb(72, 122, 51); font-style: italic; text-transform: capitalize;"><?php echo "{$_SESSION['nom']} {$_SESSION['prenom']}";?></span></h1>
    <div class="cards">
        <div class="card">
            <h2>Locataire(s)</h2>

            <?php if ($count == 0) :?>
                <p>La liste utilisateur est vide.</p>
            <?php else :?>  
                <p><?php echo $count;?></p>
            <?php endif;?>

        </div>
        <div class="card">
            <h2>Reservation(s)</h2>

            <?php if ($count1 == 0) :?>
                <p>Aucune reservation pour le moment.</p>
            <?php else :?>  
                <p><?php echo $count1;?></p>
            <?php endif;?>

        </div>
        <div class="card">
            <h2>Parcelle(s)</h2>
            
            <?php if ($countparc == 0) :?>
                <p>Aucune parcelle creer sur le terrain.</p>
            <?php else :?>  
                <p><?php echo $countparc;?></p>
            <?php endif;?>
        </div>
    </div>