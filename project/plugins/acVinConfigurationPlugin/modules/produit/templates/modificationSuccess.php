<ol class="breadcrumb">
    <li>
        <a href="<?php echo url_for("produits") ?>">Produits</a>
    </li>
    <?php
    $crum = array();
    for($n = $form->getObject() ; preg_match('/declaration\/certifications/', $n->getHash()) ; $n = $n->getParent()->getParent()) {
      $crum[] = array('hash' => $n->getHash(), 'noeud' => $n, 'libelle' => ($n->getLibelle()) ? $n->getLibelle() : 'DEFAUT') ;
    }
    $crum = array_reverse($crum);
    foreach ($crum as $c) {
      $n = $c['noeud'];
      echo "<li><a href=\"".url_for('produit_modification', array('noeud' => $n->getTypeNoeud(), 'hash' => $produit->getHashForKey())).'">'.$c['libelle'].'</a></li>';
    } ?>
    <li class="active">
        <a href="#"><?php echo sprintf("Modification du noeud %s : %s (%s)", $form->getObject()->getTypeNoeud(), $form->getObject()->getLibelle(), $form->getObject()->getKey()) ?></a>
    </li>
</ol>

<h2><?php echo sprintf("Modification du noeud %s : %s (%s)", $form->getObject()->getTypeNoeud(), $form->getObject()->getLibelle(), $form->getObject()->getKey()) ; ?></h2>

<?php include_partial('produit/form', array('form' => $form, 'produit' => $produit)) ?>
