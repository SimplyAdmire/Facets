Facets
======

Facets is an application allowing you to import snippets of your content in order to have a specified set of elements
and pages to show as sort of Style Book. It's idea is coming from the Atomic Design principles.

Steps
=====

# Make sure you use the right Settings configuration for your project.
# Make sure you add respective XML snippets.
# Import the snippets.

Settings
========

For Settings options see the Settings.yaml of this Package.

XML
====

For examples on the XML snippets, take a look at the Resources/Private/Facets folder.
It is possible to create snippets for your basis structure (required) and for your nodeTypes (your content elements)
as well as components (full blown page tree parts with plugins etc, or just some HTML snippets).

Importing
=========

Importing can be done at basic document structure level (which sets up the basic site for this tool), on a content
level or on component level.

In order to import everything run:

	$ ./flow facets:importall


In order to import the document structure:

	$ ./flow facets:importskeleton

In order to import content:

	$ ./flow facets:importnodetype (which will ask for a nodeType argument)

In order to import components:

	$ ./flow facets:importcomponent (which will ask for a component argument and accepts a parentNodePath argument as well)

Extra's
=======

This package contains a small utility you can use to generate dummy identifiers:

	$ ./flow utility:generaterandomidentifiers (optionally add -n followed by an integer to determine how many identifiers
	will be returned, default is 10)
