SCRIPT="phpdoc/makedoc.sh";
SCRIPT_GRAPH_CLASS="phpdoc/class_graph.sh";
SCRIPT_GRAPH_DIR="phpdoc/directory_graph.sh";
SCRIPT_GRAPH_PACKAGE="phpdoc/package_graph.sh";

BASE_PATH=`dirname $0`/..
$SCRIPT $BASE_PATH "Fajr";
echo "Generating class graph"
$SCRIPT_GRAPH_CLASS $BASE_PATH/report/documentation $BASE_PATH/report/
echo "Generating dir graph"
$SCRIPT_GRAPH_DIR $BASE_PATH
echo "Generating package graph"
$SCRIPT_GRAPH_PACKAGE $BASE_PATH
