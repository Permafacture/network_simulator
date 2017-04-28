<?php
    $svgSize = array(
        'width' => '800',
        'height' => '600',
    );
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <style>

.link {
    fill: none;
    stroke: #666;
    stroke-width: 1.5px;
}
.link.zap {
    /*transition: stroke 2s ease;*/
    stroke: #f06060;
    stroke-width: 2px;
}

/*
.link.resolved {
    stroke-dasharray: 0,2 1;
}
*/

circle {
    fill: #ccc;
    stroke: #333;
    stroke-width: 1.5px;
}

text {
    font: 13px sans-serif;
    pointer-events: none;
    text-shadow: 0 1px 0 #fff, 1px 0 0 #fff, 0 -1px 0 #fff, -1px 0 0 #fff;
    stroke: none;
    fill: #06f;
}

marker#active_connection {
    fill: red;
}

        </style>
    </head>
    <body>
        <script src="d3.js"></script>

        <button onclick="do_random_linked_zaps()">Random Connection</button>
        <button onclick="do_random_linked_zaps_new_only()">Random Unvisited Connection</button>

        <svg height="<?= $svgSize['height'] ?>" width="<?= $svgSize['width'] ?>">
            <defs>
                <!--<marker orient="auto"
                        markerHeight="8" markerWidth="8"
                        refY="-0.6" refX="19"
                        viewBox="0 -5 10 10"
                        id="connection"
                >
                    <path d="M-10,-10L10,0L-10,10"></path>
                </marker>-->
                <marker orient="auto"
                        markerHeight="8" markerWidth="8"
                        refY="-0.6" refX="19"
                        viewBox="0 -5 10 10"
                        id="active_connection"
                >
                    <path d="M-10,-10L10,0L-10,10"></path>
                </marker>
            </defs>
        </svg>

        <script>
{


    function array_rand(items) {
        var idx = Math.floor(Math.random() * items.length);
        var item = items[idx];
        return item;
    }

    function array_rand_w_idx(items) {
        var idx = Math.floor(Math.random() * items.length);
        var item = items[idx];
        return {item: item, idx: idx};
    }


    function node(name, connected_nodes) {
        var this_node = {
            name: name,
            route_msg: function(msg) {
                var last_idx = connected_nodes.length-1;
                //return connected_nodes[last_idx];
                return array_rand(connected_nodes);
            },
            connect_to: function(node2) {
                connected_nodes.push(node2);
            },
            connected_nodes: connected_nodes,
        }

        // connect the other nodes to it
        for (var i=0; i<connected_nodes.length; i++) {
            that_node = connected_nodes[i];
            that_node.connect_to(this_node);
        }

        return this_node;
    }

    function ring_network() {
        var n = node('n',[]);
        var n2 = node('n2',[n]);
        var n3 = node('n3',[n2]);
        var n4 = node('n4',[n,n3]);
        return [n,n2,n3,n4];
    }

    function network_links(nodes) {
        var links = [];
        for (var i=0; i<nodes.length; i++) {
            var src_node = nodes[i];
            for (var j=0; j<src_node.connected_nodes.length; j++) {
                var dest_node = src_node.connected_nodes[j];
                links.push({
                    source: src_node,
                    target: dest_node,
                    type: "connection"
                });
            }
        }
        return links;
    }


    // http://blog.thomsonreuters.com/index.php/mobile-patent-suits-graphic-of-the-day/
    var nodes = ring_network();
    var links = network_links(nodes);
    /*[
      {source: "Laptop 0", target: "Home Router", type: "connection"},
      {source: "Home Router", target: "Laptop 0", type: "connection"},

      {source: "Laptop 1", target: "Home Router", type: "connection"},
      {source: "Home Router", target: "Laptop 1", type: "connection"},

      {source: "Home Router", target: "ISP 1", type: "connection"},
      {source: "ISP 1", target: "Home Router", type: "connection"},

      {source: "ISP 1", target: "ISP 2", type: "connection"},
      {source: "ISP 2", target: "ISP 1", type: "connection"},

      {source: "ISP 1", target: "ISP 0", type: "connection"},
      {source: "ISP 0", target: "ISP 1", type: "connection"},

      {source: "ISP 2", target: "ISP 3", type: "connection"},
      {source: "ISP 3", target: "ISP 2", type: "connection"},

      {source: "ISP 3", target: "Office Router", type: "connection"},
      {source: "Office Router", target: "ISP 3", type: "connection"},

      {source: "Office Router", target: "Laptop 2", type: "connection"},
      {source: "Laptop 2", target: "Office Router", type: "connection"},

      {source: "Office Router", target: "Sprout", type: "connection"},
      {source: "Sprout", target: "Office Router", type: "connection"},

      {source: "Office Router", target: "Laptop 3", type: "connection"},
      {source: "Laptop 3", target: "Office Router", type: "connection"}
    ];*/

    { // build linksBySource
        var linkIdxsBySource = {};
        for (var i=0; i<links.length; i++) {
            link = links[i];
            if (!(link.source.name in linkIdxsBySource)) {
                linkIdxsBySource[link.source.name] = [];
            }
            linkIdxsBySource[link.source.name].push(i);
        }
    }


    var width = <?= $svgSize['width'] ?>,
        height = <?= $svgSize['height'] ?>;

    var force = d3.layout
                    .force()
                        .nodes(d3.values(nodes))
                        .links(links)
                        .size([width, height])
                        .linkDistance(100)
                        .charge(-800)
                        .on("tick", tick)
                        .start();

    /*
    var svg = d3.select("body").append("svg")
        .attr("width", width)
        .attr("height", height);

    // Per-type markers, as they don't inherit styles.
    var defs = svg.append("defs")
    */

    var svg = d3.select('svg');
    var defs = d3.select('defs');

    /*
    defs.selectAll("marker")
        .data(["connection"])
      .enter().append("marker")
        .attr("id", function(d) { return d; })
        .attr("viewBox", "0 -5 10 10")
        .attr("refX", 19)
        .attr("refY", -0.6)
        .attr("markerWidth", 8)
        .attr("markerHeight", 8)
        .attr("orient", "auto")
      .append("path")
        .attr("d", "M-10,-10L10,0L-10,10");
    */

    var connections = svg.append("g")
                            .attr("id", "connections");
    var path = connections
                    .selectAll("path")
                        .data(links) //force.links())
                      .enter().append("path")
                        .attr("class", function(d) {
                            return "link " + d.type;
                        })
                        .attr("marker-end", function(d) {
                            return "url(#" + d.type + ")";
                        });

    var gnodes = svg.append("g")
                        .attr("id", "nodes");
    var circle = gnodes.selectAll("circle")
        .data(force.nodes())
      .enter().append("circle")
        //.attr("cx", 640)
        //.attr("cy", 480)
        .attr("r", 12)
        .call(force.drag);

    var text = svg.append("g").selectAll("text")
        .data(force.nodes())
      .enter().append("text")
        .attr("text-anchor", "middle")
        .attr("x", 0)
        .attr("y", -15) // can use em too: "1.5em"
        .text(function(d) { return d.name; });

    var stillTick = true;

    // Use elliptical arc path segments to doubly-encode directionality.
    function tick() {
        if (stillTick) {
            path.attr("d", linkArc);
            circle.attr("transform", transform);
            text.attr("transform", transform);
        }
    }

    /*setTimeout(function(){
        stillTick = false;
    }, 5000);*/

    function linkArc(d) {
        var dx = d.target.x - d.source.x,
            dy = d.target.y - d.source.y,
            dr = Math.sqrt(dx * dx + dy * dy);
        var flatness = 500; // used to be 1.5, #todo use lines instead of arc and fix z-index
        return "M" + d.source.x + "," + d.source.y
                   + "A" + (dr*flatness) + "," + (dr*flatness) + " 0 0,1 "
                   + d.target.x + "," + d.target.y;
    }

    function transform(d) {
      return "translate(" + d.x + "," + d.y + ")";
    }





    function cloneArray(arr) {
        return arr.slice(0);
    }



    // add "active/zapping" style/status from connection
    function zap(pathElem) {
        pathElem.classList.add("zap");
        pathElem.setAttribute("marker-end", "url(#active_connection)");
    }

    // remove "active/zapping" style/status from connection
    function unzap(pathElem) {
        pathElem.classList.remove("zap");
        pathElem.setAttribute("marker-end", "url(#connection)");
    }

    /*
    function do_seq_zaps() {
        var connections = document.getElementById('connections');
        var paths = connections.getElementsByTagName('path');
        console.log('paths.length',paths.length);
        var i = undefined;
        var intervalId = setInterval(function(){
            console.log('i',i);

            if (i !== undefined) {
                unzap(paths[i]);
            }

            { // increment i
                if (i === undefined
                    || i >= paths.length - 1 // loop back
                ) {
                    i = 0;
                }
                else {
                    i++;
                }
            }

            zap(paths[i]);
        }, 800);
    }
    //setTimeout(do_seq_zaps, 1500);
    */

    /*
    function do_random_linked_zaps() {
        var connections = document.getElementById('connections');
        var paths = connections.getElementsByTagName('path');
        var nodeKey = 'n'; //'Laptop 0';
        var i = undefined;

        var intervalId = setInterval(function(){
            console.log('nodeKey',nodeKey);

            if (i !== undefined) {
                console.log('unzapping link ' + i);
                unzap(paths[i]);
            }

            { // advance i
                var possible_link_idxs = linkIdxsBySource[nodeKey];
                console.log('possible_link_idxs', possible_link_idxs);
                i = array_rand(possible_link_idxs);
                console.log('i', i);
                var link = links[i];
                console.log('link', link);
                nodeKey = link.target.name;
                console.log('nodeKey', nodeKey);
            }

            zap(paths[i]);
        }, 400);
    }
    //setTimeout(do_random_linked_zaps, 1500);
    */


    /*
    // same as above but don't go where you've already been
    function do_random_linked_zaps_new_only() {
        var connections = document.getElementById('connections');
        var paths = connections.getElementsByTagName('path');
        var nodeKey = 'n'; //'Laptop 0';
        var i = undefined;

        var visitedKeys = {};
        visitedKeys[nodeKey] = true;

        var intervalId = setInterval(function(){
            console.log('nodeKey',nodeKey);

            if (i !== undefined) {
                console.log('unzapping link ' + i);
                unzap(paths[i]);
            }

            { // advance i
                var possible_link_idxs = cloneArray(linkIdxsBySource[nodeKey]);
                console.log('possible_link_idxs', possible_link_idxs);
                console.log('visitedKeys', visitedKeys);

                // try to choose a place we haven't been
                loopCountDown = 100;
                while (true) {
                    if (possible_link_idxs.length == 0) {
                        console.log('ran out of paths');
                        clearInterval(intervalId);
                        return;
                    }

                    var i_and_ii = array_rand_w_idx(possible_link_idxs);
                    i = i_and_ii['item'];
                    ii = i_and_ii['idx'];

                    console.log('i', i);
                    var link = links[i];
                    console.log('link', link);
                    var would_be_target = link.target.name;
                    console.log('would_be_target', would_be_target);

                    if (would_be_target in visitedKeys) {
                        console.log('already visited');
                        possible_link_idxs.splice(ii, 1); // remove item
                        console.log('now possible_link_idxs', possible_link_idxs);
                    }
                    else {
                        console.log("haven't visited, going for it");
                        break;
                    }

                    { // sanity count down - avoid infinite loop
                        loopCountDown--;
                        if (loopCountDown == 0) {
                            alert('bailing after loopCountDown');
                            clearInterval(intervalId);
                            return;
                        }
                    }
                }

                nodeKey = would_be_target;
                visitedKeys[would_be_target] = true; // been there now
            }

            zap(paths[i]);
        }, 250);
    }
    //setTimeout(do_random_linked_zaps_new_only, 1500);
    */

    function do_zaps() {
        var connections = document.getElementById('connections');
        var paths = connections.getElementsByTagName('path');
        var node = nodes[0];
        //var nodeKey = 'n'; //'Laptop 0';
        var i = undefined;

        //var visitedKeys = {};
        //visitedKeys[nodeKey] = true;

        var intervalId = setInterval(function(){
            //console.log('nodeKey',nodeKey);
            console.log('node',node);

            if (i !== undefined) {
                console.log('unzapping link ' + i);
                unzap(paths[i]);
            }

            { // advance i
                //var possible_link_idxs = cloneArray(linkIdxsBySource[nodeKey]);
                //console.log('possible_link_idxs', possible_link_idxs);
                //console.log('visitedKeys', visitedKeys);

                // try to choose a place we haven't been
                //while (true) {
                    /*
                    if (possible_link_idxs.length == 0) {
                        console.log('ran out of paths');
                        clearInterval(intervalId);
                        return;
                    }

                    var i_and_ii = array_rand_w_idx(possible_link_idxs);
                    i = i_and_ii['item'];
                    ii = i_and_ii['idx'];
                    */

                    

                    //console.log('i', i);
                    //var link = links[i];
                    //console.log('link', link);
                    //var would_be_target = link.target.name;
                    //console.log('would_be_target', would_be_target);

                    /*
                    if (would_be_target in visitedKeys) {
                        console.log('already visited');
                        possible_link_idxs.splice(ii, 1); // remove item
                        console.log('now possible_link_idxs', possible_link_idxs);
                    }
                    else {
                        console.log("haven't visited, going for it");
                        break;
                    }
                    */

                    /*
                    { // sanity count down - avoid infinite loop
                        loopCountDown--;
                        if (loopCountDown == 0) {
                            alert('bailing after loopCountDown');
                            clearInterval(intervalId);
                            return;
                        }
                    }
                    */
                //}

                var next_node = node.route_msg('yooo!')

                // figure out which path to light up
                var possible_links = linkIdxsBySource[node.name];
                i = undefined;
                for (var x=0; x<possible_links.length; x++) {
                    var this_i = possible_links[x];
                    var this_link = links[this_i];
                    if (this_link.target == next_node) {
                        i = this_i;
                        break;
                    }
                }
                if (i === undefined) alert('noooo!');

                // here we goo!!!
                node = next_node;

                //nodeKey = would_be_target;
                //visitedKeys[would_be_target] = true; // been there now
            }

            zap(paths[i]);
        }, 250);
    }
}
        </script>
    </body>
</html>

