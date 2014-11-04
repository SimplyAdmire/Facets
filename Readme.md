SimplyAdmire.Facets
===


Installation:
---

composer require simplyadmire/facets

Loading your css and js:
---

    SimplyAdmire:
      Facets:
        contexts:
          default:
            includes:
              styles:
                main: 'resource://My.Package/Public/Styles/main.css'
              headerScripts:
                headerIncludes: 'resource://My.Package/Public/Scripts/headerIncludes.js'
              footerScripts:
                requirejs: 'resource://My.Package/Public/Library/require.js'
                site: 'resource://My.Package/Public/Scripts/footerIncludes.js'

Loading additional TypoScript:
---

    SimplyAdmire:
      Facets:
        contexts:
          default:
            includes:
              typoScripts:
                someExtraTypoScriptInclude: 'resource://My.Package/Private/TypoScript/Includes.ts2'

Register a static HTML component:
---

yaml:

    SimplyAdmire:
      Facets:
        contexts:
          default:
            data:
              components:
                nameOfTheElement:
                  resource: 'resource://My.Package/Private/Facets/Content/MyElementName.xml'
                  parentNodePath: '/sites/facets'

html:

    <h1>My HTML</h1>

xml:

    <?xml version="1.0" encoding="UTF-8"?>
    <root>
    	<node identifier="0ca1d01e-5b2c-8cc0-87d7-e59f0ec80f78" type="SimplyAdmire.Facets:Page" nodeName="general-elements">
    		<properties>
    			<title>General Elements</title>
    		</properties>
    		<node identifier="5b53ad53-1d80-7c93-dc0f-5977b715823f" type="TYPO3.Neos:ContentCollection" nodeName="main">
    			<node identifier="0f1466c6-897f-4715-b6a2-c73ea01b10a2" type="TYPO3.Neos.NodeTypes:Headline" nodeName="headline">
    				<properties>
    					<title><![CDATA[<h1>General elements</h1>]]></title>
    				</properties>
    			</node>
    			<node identifier="0fa980a0-8f4f-a062-1f68-af40979870c4" type="TYPO3.Neos.NodeTypes:Text" nodeName="introduction">
    				<properties>
    					<text><![CDATA[<p>Lorem ipsum...</p>]]></text>
    				</properties>
    			</node>
    		</node>
    		<node identifier="c6bb881c-9bbe-5562-a93e-4c714e55ee25" type="SimplyAdmire.Facets:Page" nodeName="typography">
    			<properties>
    				<title>Typography</title>
    			</properties>
    			<node identifier="1e00ad08-3521-89b3-091f-8c6a518d9300" type="TYPO3.Neos:ContentCollection" nodeName="main">
    				<node identifier="d73e1494-0db2-6898-18a2-fa26b67dc73c" type="TYPO3.Neos.NodeTypes:Headline" nodeName="headline">
    					<properties>
    						<title><![CDATA[<h1>TypoGraphy</h1>]]></title>
    					</properties>
    				</node>
    				<node identifier="3d97fc4e-b775-c1a2-edc1-29e24533c0c4" type="TYPO3.Neos.NodeTypes:Text" nodeName="introduction">
    					<properties>
    						<text><![CDATA[<p>Hieronder vindt u diverse <em>typografische</em> elementen zoals die op de website worden gebruikt.</p>]]></text>
    					</properties>
    				</node>
    				<node identifier="32b878b7-10c0-adee-69e2-a606b9104e85" type="SimplyAdmire.Facets:HtmlFacet" nodeName="facet">
    					<properties>
    						<templatePath>resource://My.Package/Private/Facets/Templates/GeneralElements/TypoGraphy.html</templatePath>
    					</properties>
    				</node>
    			</node>
    		</node>
    	</node>
    </root>