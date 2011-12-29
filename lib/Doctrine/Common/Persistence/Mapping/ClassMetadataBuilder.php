<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Common\Persistence\Mapping;

/**
 * Builder interface for class metadata instances used for constructing
 * metadata.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
interface ClassMetadataBuilder
{
    /**
     * Initialize Reflection based parameters of the underlying metadata.
     *
     * Method is only called on non-cache construction of a new metadata
     * instance. The data set here should be serialized.
     *
     * @param ReflectionService $reflService
     * @return void
     */
    function initializeReflection(ReflectionService $reflService);

    /**
     * Wakeup Reflection parameters after reconstituting the metdata from cache
     *
     * @param ReflectionService $reflService
     * @return void
     */
    function wakeupReflection(ReflectionService $reflService);

    /**
     * Get the metadata class that belongs to this builder.
     *
     * If the builder and metadata are implemented on the same class this
     * method has to just return $this.
     *
     * @return ClassMetadata
     */
    function getWrappedClassMetadata();
}

