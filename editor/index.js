// WP Bodybuilder's editor script entry point
import { useBlockProps } from "@wordpress/block-editor";
import {
  registerBlockType,
  __experimentalSanitizeBlockAttributes,
} from "@wordpress/blocks";
import { ServerSideRender } from "@wordpress/server-side-render";
import apiFetch from "@wordpress/api-fetch";
import { addQueryArgs } from "@wordpress/url";
import fastDeepEqual from "fast-deep-equal/es6";
import { RawHTML, useState, useEffect, useRef } from "@wordpress/element";
import { useDebounce, usePrevious } from "@wordpress/compose";
import bringHtmlToLife from "./html-parser";
import { Spinner } from "@wordpress/components";

export function rendererPath(block, attributes = null, urlQueryArgs = {}) {
  return addQueryArgs(`/wp/v2/block-renderer/${block}`, {
    context: "edit",
    ...(null !== attributes ? { attributes } : {}),
    ...urlQueryArgs,
  });
}

// Most of this code is taken from @wordpress/server-side-render
// So it doesn't look very good and could use some refactoring
const registerBlock = (name) => {
  console.log("Registering block", name);
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

      useEffect(() => {
        if (hasResponse) {
          debouncedFetchData();
        } else {
          fetchData();
        }
      }, []); // Only on non-rich-text attributes

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

      return React.cloneElement(
        bringHtmlToLife(response, props.attributes, props.setAttributes),
        blockProps
      );
    },
    save: () => null,
  });
};

const registerBlocks = (names) => names.forEach((name) => registerBlock(name));

window.bodybuilder = {
  register_block: registerBlock,
  register_blocks: registerBlocks,
};
