import { ProcessNodeDefinitions, Parser } from "html-to-react";
import { RichText } from "@wordpress/block-editor";
import { __ } from "@wordpress/i18n";

const htmlToReactParser = new Parser();
const richTextTags = ["p", "h1", "h2", "h3", "h4", "h5", "h6"];

/**
 * Turns HTML into React element, with all necessary rich stuff
 * Makes x-rich text blocks work
 *
 * @param {string} htmlInput The HTML in string form (from WP API)
 */
export default function bringHtmlToLife(
  htmlInput,
  attributes = {},
  setAttributes = () => undefined
) {
  const processNodeDefinitions = new ProcessNodeDefinitions();
  const processingInstructions = [
    {
      // Custom <h1> processing
      shouldProcessNode: (node) =>
        richTextTags.includes(node.name) &&
        Object.keys(node.attribs).includes("wp-rich"),
      processNode: (node, children) => {
        const attributeName = node.attribs["wp-rich"];
        const formats =
          node.attribs["wp-rich-formats"]
            ?.split(",")
            .map((format) =>
              format.includes("/") ? format : `core/${format}`
            ) ?? [];

        return (
          <RichText
            tagName={node.name} // The tag here is the element output and editable in the admin
            value={attributes[attributeName] ?? ""} // Any existing content, either from the database or an attribute default
            allowedFormats={formats} // Allow the content to be made bold or italic, but do not allow other formatting options
            onChange={(content) => setAttributes({ [attributeName]: content })} // Store updated content as a block attribute
            placeholder={
              node.attribs["wp-placeholder"] ?? __("Start writing...")
            } // Display this text before any content has been added by the user
          />
        );
      },
    },
    {
      // Anything else
      shouldProcessNode: function (node) {
        return true;
      },
      processNode: processNodeDefinitions.processDefaultNode,
    },
  ];

  return htmlToReactParser.parseWithInstructions(
    htmlInput,
    () => true,
    processingInstructions
  );
}
