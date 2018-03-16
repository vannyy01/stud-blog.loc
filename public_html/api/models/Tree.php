<?php
namespace api\models;
use api\models\Node;
use phpDocumentor\Reflection\Types\Null_;

class Tree
{
    public $root = NULL;
    public function insert ($value)
    {
        $node = new Node($value);
        $this->add_Node($node, $this->root);
    }
        public function add_Node(Node $Tree, &$subtree){
            if(is_null($subtree)){
                $subtree = $Tree;
                return;
            }
// tree exists
            if($Tree->value->parent_id > $subtree->value->parent_id){
                $Tree->push($Tree);
                //var_dump($Tree->value);die();
                foreach ($Tree->son as $son){
                    $this->add_Node($Tree, $son);
                }
            }

        }

        public static function getChild($parent){
            $array = [];
            $i = 0;

        }
}