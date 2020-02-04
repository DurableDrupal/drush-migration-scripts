#!/usr/bin/drush

/**
 * Sample execution from document root (full, with node_load):
 *
 *   ./scripts/dd-get-books.php > scripts/data/inv-content-all-books.txt
 *   ./scripts/dd-get-books.php 321 > scripts/data/inv-content-by-book-321.txt
 */
$args = drush_get_arguments();
$book = $args[2];

if($book) {
  print_r(menu_tree_all_data(book_menu_name($args[2])));
} else {
  // Just lists all book nids and says if it has children
  // print_r(book_get_books());
  $result = db_query("SELECT DISTINCT(bid) FROM {book}");
  while ($book = db_fetch_array($result)) {
    $nids[] = $book['bid'];
  }
  print_r($nids);
  foreach($nids as $nid) {
    $n = node_load($nid);
    print("----------------------------------\n");
    print 'Book: ' . $n->title . "\n";
    print("----------------------------------\n");
    print_r(menu_tree_all_data(book_menu_name($nid)));
    print("----------------------------------\n\n");
  }
}

 

