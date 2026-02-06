<?php
// Fix body-map.html.twig
$bodyMap = file_get_contents('templates/health/accessible/body-map.html.twig');
// Find the position after {% endblock %} that closes the body block
$pos = strpos($bodyMap, '{% endblock %}') + strlen('{% endblock %}');
// Insert javascripts block after body block
$scriptBlock = "\n{% block javascripts %}\n    {{ parent() }}\n    <script src=\"{{ asset('build/accessibility.js') }}\"></script>\n";
$bodyMap = substr_replace($bodyMap, $scriptBlock, $pos, 0);
// Remove the duplicate javascripts block at the end
$bodyMap = preg_replace('/\n{% block javascripts %}[\s\S]*?{% endblock %}\s*$/', "{% endblock %}", $bodyMap);
file_put_contents('templates/health/accessible/body-map.html.twig', $bodyMap);

echo "Fixed body-map.html.twig\n";

// Fix journal-entry.html.twig
$journal = file_get_contents('templates/health/accessible/journal-entry.html.twig');
$pos = strpos($journal, '{% endblock %}') + strlen('{% endblock %}');
$scriptBlock = "\n{% block javascripts %}\n    {{ parent() }}\n    <script src=\"{{ asset('build/accessibility.js') }}\"></script>\n";
$journal = substr_replace($journal, $scriptBlock, $pos, 0);
$journal = preg_replace('/\n{% block javascripts %}[\s\S]*?{% endblock %}\s*$/', "{% endblock %}", $journal);
file_put_contents('templates/health/accessible/journal-entry.html.twig', $journal);

echo "Fixed journal-entry.html.twig\n";
echo "Done!\n";
