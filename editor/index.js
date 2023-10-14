// WP Bodybuilder's editor script entry point
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import {
  registerBlockType,
  __experimentalSanitizeBlockAttributes,
} from "@wordpress/blocks";
import { ServerSideRender } from "@wordpress/server-side-render";
import apiFetch from "@wordpress/api-fetch";
import { addQueryArgs } from "@wordpress/url";
import fastDeepEqual from "fast-deep-equal/es6";
import {
  RawHTML,
  useState,
  useEffect,
  useRef,
  useMemo,
} from "@wordpress/element";
import { useDebounce, usePrevious } from "@wordpress/compose";
import bringHtmlToLife from "./html-parser";
import {
  Spinner,
  TextControl,
  PanelBody,
  PanelRow,
  ToggleControl,
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";

export function rendererPath(block, attributes = null, urlQueryArgs = {}) {
  return addQueryArgs(`/wp/v2/block-renderer/${block}`, {
    context: "edit",
    ...(null !== attributes ? { attributes } : {}),
    ...urlQueryArgs,
  });
}

// Most of this code is taken from @wordpress/server-side-render
// So it doesn't look very good and could use some refactoring
const registerBlock = (name, attributes) => {
  console.log("Registering block", name, attributes);
  registerBlockType(name, {
    edit: (props) => {
      const blockProps = useBlockProps({
        className: "bodybuilder-block",
      });
      const fetchRequestRef = useRef();
      const isMountedRef = useRef(true);
      const [response, setResponse] = useState(null);

      function fetchData() {
        let sanitizedAttributes =
          props.attributes &&
          __experimentalSanitizeBlockAttributes(name, props.attributes);

        const urlAttributes = sanitizedAttributes ?? null;
        const path = rendererPath(name, urlAttributes, undefined);
        const data = null;

        // Store the latest fetch request so that when we process it, we can
        // check if it is the current request, to avoid race conditions on slow networks.
        const fetchRequest = (fetchRequestRef.current = apiFetch({
          path,
          data,
          method: "GET",
        })
          .then((fetchResponse) => {
            if (
              isMountedRef.current &&
              fetchRequest === fetchRequestRef.current &&
              fetchResponse
            ) {
              setResponse(fetchResponse.rendered);
            }
          })
          .catch((error) => {
            if (
              isMountedRef.current &&
              fetchRequest === fetchRequestRef.current
            ) {
              setResponse({
                error: true,
                errorMsg: error.message,
              });
            }
          }));

        return fetchRequest;
      }

      const debouncedFetchData = useDebounce(fetchData, 500);
      const hasResponse = !!response;

      const sidebarAttributes = useMemo(() => {
        console.log(attributes, props.attributes, {
          attributes,
          ...props.attributes,
        });
        return Object.keys(attributes)
          .map((key) => ({
            name: key,
            value: props.attributes[key],
            ...attributes[key],
          }))
          .filter((i) => i["bb-type"] === "sidebar");
      }, [props]);

      useEffect(() => {
        if (hasResponse) {
          debouncedFetchData();
        } else {
          fetchData();
        }
      }, [sidebarAttributes]); // Only on non-rich-text attributes

      if (!hasResponse) {
        return (
          <div {...blockProps}>
            <Spinner />
          </div>
        );
      }

      if (response.error) {
        return <div {...blockProps}>Error: {response.errorMsg}</div>;
      }

      const serialized = bringHtmlToLife(
        response,
        props.attributes,
        props.setAttributes
      );

      return (
        <>
          {React.cloneElement(serialized, {
            ...blockProps,
            children: [
              ...serialized.props.children,
              sidebarAttributes.length > 0 ? (
                <InspectorControls key={name}>
                  <PanelBody
                    title={__("Block Settings", "bodybuilder")}
                    initialOpen={true}
                  >
                    {sidebarAttributes.map((attribute) => (
                      <PanelRow>
                        <fieldset key={attribute.name}>
                          {attribute.type === "string" && (
                            <TextControl
                              label={__(attribute.name, "bodybuilder")}
                              value={props.attributes[attribute.name]}
                              onChange={(val) =>
                                props.setAttributes({ [attribute.name]: val })
                              }
                            />
                          )}
                          {attribute.type === "boolean" && (
                            <ToggleControl
                              label={__(attribute.name, "bodybuilder")}
                              checked={props.attributes[attribute.name]}
                              onChange={(val) =>
                                props.setAttributes({ [attribute.name]: val })
                              }
                            />
                          )}
                        </fieldset>
                      </PanelRow>
                    ))}
                  </PanelBody>
                </InspectorControls>
              ) : (
                <></>
              ),
            ],
          })}
        </>
      );
    },
    save: () => null,
  });
};

const registerBlocks = (blocks) => {
  console.log(blocks);
  Object.keys(blocks).forEach((name) => registerBlock(name, blocks[name]));
};

window.bodybuilder = {
  register_block: registerBlock,
  register_blocks: registerBlocks,
};
