<h1>Vue d'ensemble</h1>
        <div class="cards">
            <div class="card">
                <h2>Locataire(s)</h2>
                <?php if ($count == 0) :?>
                    <p>La liste utilisateur est vide.</p>
                
                <?php else :?>  
                    <p><?php echo "$count"?></p>
                <?php endif;?>
            </div>
            <div class="card">
                <h2>Reservation(s)</h2>
                <p>320</p>
            </div>
            <div class="card">
                <h2>Parcelle(s)</h2>
                <p>128</p>
            </div>
        </div>